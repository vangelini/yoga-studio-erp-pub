<?php

namespace App\Services;

use App\Mail\NotificationMessageMail;
use App\Models\Notification;
use App\Models\Course;
use App\Models\NotificationDispatch;
use App\Models\NotificationJob;
use App\Models\NotificationCourseTarget;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Dispatch a notification across configured channels.
     */
    public function dispatch(Notification $notification, ?User $initiator = null, array $context = []): NotificationJob
    {
        $channels = $notification->channels ?? [];
        if ($notification->trigger_type === 'event') {
            $channels = array_values(array_filter($channels, fn ($channel) => $channel !== 'whatsapp'));
        }
        if (empty($channels)) {
            throw new \InvalidArgumentException('La notifica non ha canali configurati.');
        }

        $paymentsByUser = collect($context['payments'] ?? [])->filter()->groupBy('user_id');
        $baseContext = Arr::except($context, ['payments']);

        $recipients = $this->resolveRecipients($notification, $initiator, $context);
        $job = NotificationJob::create([
            'notification_id' => $notification->id,
            'status' => $recipients->isEmpty() ? 'completed' : 'pending',
            'trigger_type' => $notification->trigger_type,
            'scheduled_at' => now(),
            'initiated_by' => $initiator?->id,
            'target_count' => $recipients->count(),
        ]);

        if ($recipients->isEmpty()) {
            return $job;
        }

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $user) {
            $userContext = $baseContext;
            if ($paymentsByUser->has($user->id)) {
                $userPayments = $paymentsByUser->get($user->id);
                $userContext['payment'] = $userPayments->first();
                $userContext['payment_list'] = $userPayments->values()->all();
            }

            foreach ($channels as $channel) {
                $dispatch = NotificationDispatch::create([
                    'notification_job_id' => $job->id,
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'channel' => $channel,
                ]);

                $message = $this->buildMessage($notification, $user, $userContext);

                try {
                    match ($channel) {
                        'portal' => $this->sendPortal($dispatch, $message, $userContext),
                        'email' => $this->sendEmail($dispatch, $user, $notification->title, $message),
                        'whatsapp' => $this->sendWhatsapp($dispatch, $user, $message),
                        default => $this->markFailed($dispatch, 'Canale non supportato'),
                    };

                    if ($dispatch->status === 'failed') {
                        $failed++;
                    } else {
                        $sent++;
                    }
                } catch (\Throwable $exception) {
                    $this->markFailed($dispatch, $exception->getMessage());
                    $failed++;
                }
            }
        }

        $job->update([
            'status' => $failed > 0 && $sent === 0 ? 'failed' : 'completed',
            'started_at' => $job->scheduled_at,
            'completed_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);

        return $job->fresh('dispatches');
    }

    /**
     * Resolve recipients for a given notification.
     */
    public function resolveRecipients(Notification $notification, ?User $initiator = null, array $context = []): Collection
    {
        $users = collect();

        if ($notification->target_all_teachers) {
            $users = $users->merge(
                User::query()->where('role', 'Teacher')->get()
            );
        }

        if ($notification->target_all_clients) {
            $users = $users->merge(
                User::query()->where('role', 'Client')->get()
            );
        }

        if ($notification->target_all_admins) {
            $users = $users->merge(
                User::query()->where('role', 'Admin')->get()
            );
        }

        if ($notification->courseTargets()->exists()) {
            $courseIds = $notification->courseTargets()->pluck('course_id');
            if ($initiator && $initiator->role === 'Teacher') {
                $teacherCourseIds = Course::where('teacher_id', $initiator->id)->pluck('id');
                $courseIds = $courseIds->intersect($teacherCourseIds);
            }

            if ($courseIds->isNotEmpty()) {
                $courseClients = Subscription::query()
                    ->select('client_id')
                    ->whereIn('course_id', $courseIds)
                    ->where('status', '!=', 'cancelled')
                    ->distinct()
                    ->pluck('client_id');

                if ($courseClients->isNotEmpty()) {
                    $users = $users->merge(User::whereIn('id', $courseClients)->get());
                }
            }
        }

        if (!empty($notification->target_user_ids)) {
            $users = $users->merge(
                User::whereIn('id', $notification->target_user_ids)->get()
            );
        }

        $users = $users->filter();

        if ($initiator && $initiator->role === 'Teacher' && !$notification->target_all_teachers) {
            // teachers can message themselves for internal awareness
            $users->push($initiator);
        }

        return $users
            ->unique('id')
            ->values();
    }

    public function handleEvent(string $eventType, array $context = []): void
    {
        Notification::query()
            ->active()
            ->where('trigger_type', 'event')
            ->where('event_type', $eventType)
            ->get()
            ->each(function (Notification $notification) use ($context) {
                $this->dispatch($notification, null, $context);
            });
    }

    public function sendOverdueNotifications(?int $days = null): void
    {
        $days = $days ?? (int) (optional(Setting::query()->find('notification_overdue_days'))->value ?? 7);
        $limit = now()->copy()->subDays(max(1, $days));
        $payments = Payment::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', $limit)
            ->get();

        if ($payments->isEmpty()) {
            return;
        }

        $message = optional(Setting::query()->find('notification_overdue_message'))->value ?? 'Hai un pagamento in sospeso.';
        $notification = $this->ensureSystemNotification('overdue_payments', $message, ['portal']);

        $this->dispatch($notification, null, ['payments' => $payments->toArray()]);
    }

    public function sendPendingPaymentNotification(array $paymentIds): void
    {
        $payments = Payment::query()
            ->whereIn('id', $paymentIds)
            ->where('status', 'pending')
            ->get();

        if ($payments->isEmpty()) {
            return;
        }

        $message = optional(Setting::query()->find('notification_pending_message'))->value ?? 'Sono state generate nuove pendenze.';
        $notification = $this->ensureSystemNotification('pending_payments_generated', $message, ['portal']);

        $this->dispatch($notification, null, ['payments' => $payments->toArray()]);
    }

    protected function sendPortal(NotificationDispatch $dispatch, string $message, array $context = []): void
    {
        $dispatch->update([
            'status' => 'sent',
            'sent_at' => now(),
            'payload' => [
                'message' => $message,
                'context' => $context,
            ],
        ]);
    }

    protected function sendEmail(NotificationDispatch $dispatch, User $user, string $subject, string $message): void
    {
        if (!$user->email) {
            $this->markFailed($dispatch, 'Email utente non disponibile');
            return;
        }

        Mail::to($user->email)->queue(new NotificationMessageMail($subject, $message));

        $dispatch->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    protected function sendWhatsapp(NotificationDispatch $dispatch, User $user, string $message): void
    {
        if (!$user->telephone) {
            $this->markFailed($dispatch, 'Numero telefono non disponibile');
            return;
        }

        Log::info('WhatsApp notification queued', [
            'user_id' => $user->id,
            'message' => $message,
        ]);

        $dispatch->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    protected function markFailed(NotificationDispatch $dispatch, string $error): void
    {
        $dispatch->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    protected function buildMessage(Notification $notification, User $recipient, array $context = []): string
    {
        $message = $notification->message_body;

        $placeholders = [
            'user.name' => $recipient->name,
            'user.email' => $recipient->email,
        ];

        if ($client = Arr::get($context, 'client')) {
            $placeholders['client.name'] = Arr::get($client, 'name', Arr::get($client, 'full_name', ''));
            $placeholders['client.email'] = Arr::get($client, 'email', '');
        }

        if ($course = Arr::get($context, 'course')) {
            $placeholders['course.title'] = $course['title'] ?? Arr::get($course, 'title');
        }

        if ($subscription = Arr::get($context, 'subscription')) {
            $placeholders['subscription.plan'] = $subscription['plan_label'] ?? Arr::get($subscription, 'plan_label');
        }

        if ($payment = Arr::get($context, 'payment')) {
            $placeholders['payment.due_date'] = optional(Arr::get($payment, 'due_date')) ? Carbon::parse($payment['due_date'])->format('d/m/Y') : '';
            $placeholders['payment.amount'] = number_format((float) ($payment['amount'] ?? 0), 2, ',', '.');
        }

        $aliases = [
            'corso.title' => 'course.title',
            'corse.title' => 'course.title',
            'corso.nome' => 'course.title',
            'allievo.nome' => 'client.name',
            'allievo.email' => 'client.email',
        ];
        foreach ($aliases as $alias => $target) {
            if (isset($placeholders[$target])) {
                $placeholders[$alias] = $placeholders[$target];
            }
        }

        $message = preg_replace_callback('/{{\s*([a-z0-9_.]+)\s*}}/i', function ($matches) use ($placeholders) {
            $key = strtolower($matches[1]);
            return array_key_exists($key, $placeholders) ? (string) $placeholders[$key] : $matches[0];
        }, $message);

        return rtrim($message) . "\n";
    }

    public function buildWhatsappLinks(Notification $notification, ?User $initiator = null, array $context = []): array
    {
        return $this->resolveRecipients($notification, $initiator, $context)
            ->map(function (User $user) use ($notification, $context) {
                $raw = preg_replace('/\\D+/', '', (string) $user->telephone);
                if (!$raw) {
                    return null;
                }

                $message = $this->buildMessage($notification, $user, $context);
                $message = trim(preg_replace('/(\r\n|\r|\n)/', "\n", $message));
                $url = sprintf('https://wa.me/%s?text=%s', $raw, rawurlencode($message));

                return [
                    'user' => $user,
                    'url' => $url,
                    'message' => $message,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
