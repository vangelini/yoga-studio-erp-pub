<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notification;
use App\Models\NotificationCourseTarget;
use App\Models\NotificationDispatch;
use App\Models\NotificationJob;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $this->authorizeAccess($user);

        $notificationsQuery = Notification::query()
            ->with(['jobs' => fn ($query) => $query->latest()->limit(1), 'courseTargets'])
            ->orderByDesc('is_system')
            ->orderBy('created_at', 'desc');

        if ($user->role === 'Teacher') {
            $notificationsQuery->where(function ($query) use ($user) {
                $query->where('is_system', true)
                    ->orWhere('created_by', $user->id);
            });
        }

        $notifications = $notificationsQuery->get();

        $jobs = NotificationJob::with(['notification', 'dispatches.user'])
            ->latest()
            ->limit(20)
            ->get();

        $coursesQuery = Course::orderBy('title');
        if ($user->role === 'Teacher') {
            $coursesQuery->where('teacher_id', $user->id);
        }

        $eventOptions = [
            [
                'value' => '',
                'label' => '—',
                'placeholders' => [],
                'hint' => '',
            ],
            [
                'value' => 'new_client',
                'label' => 'Nuova registrazione allievo',
                'placeholders' => ['client.name', 'client.email', 'user.name', 'user.email'],
                'hint' => 'L’evento espone i dati dell’allievo appena registrato e quelli del destinatario.',
            ],
            [
                'value' => 'new_subscription',
                'label' => 'Nuova iscrizione a corso',
                'placeholders' => ['client.name', 'course.title', 'subscription.plan', 'user.name'],
                'hint' => 'Include informazioni sul corso scelto, sul piano e sull’allievo.',
            ],
            [
                'value' => 'subscription_cancelled',
                'label' => 'Cancellazione corso',
                'placeholders' => ['client.name', 'course.title', 'subscription.plan', 'user.name'],
                'hint' => 'Disponibili i dettagli del corso annullato e dell’allievo coinvolto.',
            ],
            [
                'value' => 'overdue_payments',
                'label' => 'Morosità',
                'placeholders' => ['user.name', 'payment.due_date', 'payment.amount'],
                'hint' => 'Per ogni destinatario viene fornita la prima pendenza scaduta.',
            ],
            [
                'value' => 'pending_payments_generated',
                'label' => 'Pendenze generate',
                'placeholders' => ['user.name', 'payment.due_date', 'payment.amount'],
                'hint' => 'Mostra l’importo e la scadenza della pendenza appena creata.',
            ],
        ];

        $editingId = $request->query('edit') ?? $request->session()->getOldInput('notification_id');
        $editingNotification = null;
        if ($editingId) {
            $candidate = $notifications->firstWhere('id', (int) $editingId) ?? Notification::query()->with('courseTargets')->find($editingId);
            if ($candidate && ($user->role === 'Admin' || $candidate->created_by === $user->id)) {
                $editingNotification = $candidate->loadMissing('courseTargets');
            }
        }

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'jobs' => $jobs,
            'courses' => $coursesQuery->get(),
            'canBroadcastAll' => $user->role === 'Admin',
            'user' => $user,
            'eventOptions' => $eventOptions,
            'editingNotification' => $editingNotification,
        ]);
    }

    public function store(Request $request, NotificationService $service): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user);

        $editingId = $request->input('notification_id');
        $notification = null;
        if ($editingId) {
            $notification = Notification::query()->with('courseTargets')->findOrFail($editingId);
            $this->authorizeNotification($notification, $user);
            abort_unless(!$notification->is_system, 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'message_body' => ['required', 'string'],
            'trigger_type' => ['required', Rule::in(['manual', 'scheduled', 'event'])],
            'event_type' => ['nullable', 'string', 'max:255'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string', Rule::in(['portal', 'email', 'whatsapp'])],
            'target_all_teachers' => ['nullable', 'boolean'],
            'target_all_clients' => ['nullable', 'boolean'],
            'target_all_admins' => ['nullable', 'boolean'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
            'schedule_interval_value' => ['nullable', 'integer', 'min:1', 'max:365', 'required_if:trigger_type,scheduled'],
            'schedule_interval_unit' => ['nullable', Rule::in(['hour', 'day', 'week', 'month']), 'required_if:trigger_type,scheduled'],
            'schedule_time' => ['nullable', 'date_format:H:i', 'required_if:trigger_type,scheduled'],
            'send_now' => ['nullable', 'boolean'],
        ]);

        if ($user->role === 'Teacher') {
            if ($request->boolean('target_all_clients') || $request->boolean('target_all_teachers') || $request->boolean('target_all_admins')) {
                abort(403, 'Non hai i permessi per inviare notifiche globali.');
            }
        }

        if ($data['trigger_type'] === 'event' && empty($data['event_type'])) {
            return back()->withErrors(['event_type' => 'Seleziona un evento per questo tipo di notifica.'])->withInput();
        }

        if ($user->role === 'Teacher' && $data['trigger_type'] !== 'manual') {
            abort(403, 'Le notifiche programmate o evento richiedono privilegi amministrativi.');
        }

        if (!$request->boolean('target_all_teachers') && !$request->boolean('target_all_clients') && !$request->boolean('target_all_admins') && empty($data['course_ids'])) {
            return back()->withErrors(['course_ids' => 'Seleziona almeno un gruppo di destinatari.'])->withInput();
        }

        $scheduleNextRunAt = null;
        if ($data['trigger_type'] === 'scheduled') {
            $scheduleNextRunAt = $this->calculateNextRunAt(
                $data['schedule_time'],
                (int) $data['schedule_interval_value'],
                $data['schedule_interval_unit']
            );
        }

        if (!$notification) {
            $notification = new Notification(['created_by' => $user->id]);
        }

        $notification->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'trigger_type' => $data['trigger_type'],
            'event_type' => $data['trigger_type'] === 'event' ? ($data['event_type'] ?? null) : null,
            'message_body' => $data['message_body'],
            'channels' => $data['channels'],
            'target_all_teachers' => $request->boolean('target_all_teachers'),
            'target_all_clients' => $request->boolean('target_all_clients'),
            'target_all_admins' => $request->boolean('target_all_admins'),
            'is_system' => false,
            'schedule_interval_unit' => $data['schedule_interval_unit'] ?? null,
            'schedule_interval_value' => $data['schedule_interval_value'] ?? null,
            'schedule_time' => $data['schedule_time'] ?? null,
            'schedule_next_run_at' => $scheduleNextRunAt,
            'is_active' => $data['trigger_type'] === 'scheduled' ? ($notification->is_active ?? true) : true,
            'updated_by' => $user->id,
        ]);

        $notification->save();

        if ($notification->wasRecentlyCreated === false) {
            $notification->courseTargets()->delete();
        }

        $courseIds = collect($data['course_ids'] ?? []);
        if ($user->role === 'Teacher') {
            $availableCourseIds = Course::where('teacher_id', $user->id)->pluck('id');
            $courseIds = $courseIds->intersect($availableCourseIds);
        }

        $courseIds->each(function ($courseId) use ($notification) {
            NotificationCourseTarget::create([
                'notification_id' => $notification->id,
                'course_id' => $courseId,
            ]);
        });

        if ($notification->trigger_type === 'manual' && $request->boolean('send_now')) {
            $service->dispatch($notification, $user);
        }

        $message = $notification->wasRecentlyCreated ? 'Notifica salvata con successo.' : 'Notifica aggiornata con successo.';

        return redirect()->route('notifications.index')->with('status', $message);
    }

    public function send(Request $request, Notification $notification, NotificationService $service)
    {
        $user = $request->user();
        $this->authorizeNotification($notification, $user);
        abort_unless($notification->trigger_type === 'manual', 403);

        $service->dispatch($notification, $user);

        if (in_array('whatsapp', $notification->channels ?? [])) {
            $links = $service->buildWhatsappLinks($notification, $user);

            return view('admin.notifications.whatsapp-links', [
                'notification' => $notification,
                'links' => $links,
            ]);
        }

        return redirect()->route('notifications.index')->with('status', 'Notifica inviata.');
    }

    protected function authorizeAccess($user): void
    {
        abort_unless($user && in_array($user->role, ['Admin', 'Teacher']), 403);

        if ($user->role === 'Teacher') {
            $profile = $user->teacherProfile;
            abort_unless($profile && ($profile->can_manage_students || $profile->can_manage_payments), 403);
        }
    }

    public function resendPending(Request $request, NotificationService $service): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Admin', 403);

        $lastRunSetting = Setting::query()->find('course_payment_last_run');
        $payload = $lastRunSetting ? json_decode($lastRunSetting->value, true) : [];
        $ids = $payload['created_ids'] ?? [];

        if (empty($ids)) {
            return back()->with('status', 'Non ci sono pendenze recenti da notificare.');
        }

        $service->sendPendingPaymentNotification($ids);

        return back()->with('status', 'Notifiche pendenze reinviate.');
    }

    public function destroy(Request $request, Notification $notification): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeNotification($notification, $user);

        $notification->delete();

        return redirect()->route('notifications.index')->with('status', 'Notifica rimossa.');
    }

    public function purgeLogs(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Admin', 403);

        NotificationDispatch::query()->delete();
        NotificationJob::query()->delete();

        return back()->with('status', 'Log invii notifiche eliminato.');
    }

    public function toggleScheduled(Request $request, Notification $notification): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeNotification($notification, $user);
        abort_unless($notification->trigger_type === 'scheduled', 403);

        $notification->is_active = !$notification->is_active;

        if ($notification->is_active && $notification->schedule_interval_value && $notification->schedule_interval_unit) {
            $time = $notification->schedule_time ?? now()->format('H:i');
            $notification->schedule_next_run_at = $this->calculateNextRunAt(
                $time,
                (int) $notification->schedule_interval_value,
                $notification->schedule_interval_unit
            );
        }

        $notification->save();

        return back()->with('status', $notification->is_active ? 'Notifica programmata attivata.' : 'Notifica programmata disattivata.');
    }

    public function exportJobs(Request $request)
    {
        $user = $request->user();
        $this->authorizeAccess($user);

        $jobs = NotificationJob::with(['notification', 'dispatches.user'])
            ->latest()
            ->limit(200)
            ->get();

        $lines = [];
        foreach ($jobs as $job) {
            $lines[] = sprintf(
                "[%s] #%d %s | Trigger: %s | Stato: %s | Target: %d | Inviate: %d | Errore: %s",
                optional($job->completed_at)->format('Y-m-d H:i:s') ?? '----',
                $job->id,
                $job->notification->title ?? 'N/D',
                $job->trigger_type,
                $job->status,
                $job->target_count,
                $job->sent_count,
                $job->error_message ?: '-'
            );

            foreach ($job->dispatches as $dispatch) {
                $lines[] = sprintf(
                    "    - %s (%s) [%s] => %s",
                    $dispatch->user->name ?? ('Utente #' . $dispatch->user_id),
                    $dispatch->channel,
                    $dispatch->status,
                    trim($dispatch->payload['message'] ?? 'Messaggio non disponibile')
                );
            }

            $lines[] = str_repeat('-', 80);
        }

        $content = implode("\n", $lines);
        $filename = 'notification-log-' . now()->format('Ymd_His') . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    protected function authorizeNotification(Notification $notification, $user): void
    {
        $this->authorizeAccess($user);

        if ($user->role === 'Admin') {
            return;
        }

        abort_unless($notification->created_by === $user->id, 403);
    }

    private function calculateNextRunAt(string $time, int $intervalValue, string $intervalUnit): Carbon
    {
        $now = now()->timezone(config('app.timezone'));
        $next = $now->copy()->setTimeFromTimeString($time);

        if ($next->lessThanOrEqualTo($now)) {
            $next = $this->addInterval($next, $intervalValue, $intervalUnit);
        }

        return $next;
    }

    private function addInterval(Carbon $start, int $value, string $unit): Carbon
    {
        return match ($unit) {
            'hour' => $start->copy()->addHours($value),
            'day' => $start->copy()->addDays($value),
            'week' => $start->copy()->addWeeks($value),
            'month' => $start->copy()->addMonths($value),
            default => $start->copy()->addDays($value),
        };
    }
}
