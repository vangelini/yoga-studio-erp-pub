<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Course;
use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Teacher;
use App\Services\MembershipManager;
use App\Support\TimeHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardPageController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $membershipManager = app(MembershipManager::class);
        $membership = $membershipManager->ensureCurrentMembership($user, false);

        $courses = Course::with(['teacher', 'schedule'])
            ->orderBy('title')
            ->get()
            ->map(function (Course $course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'teacher_id' => $course->teacher_id,
                    'teacher_name' => optional($course->teacher)->name,
                    'price' => $course->price,
                    'speciality_description' => $course->speciality_description,
                    'gallery' => $course->gallery ?? [],
                    'schedule' => $course->schedule->map(function ($slot) {
                        return [
                            'day' => $slot->day_of_week,
                            'time' => $slot->time ? TimeHelper::format($slot->time) : null,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $teachers = Teacher::with(['user', 'availability.bookedBy'])
            ->get()
            ->map(function (Teacher $teacher) {
                return [
                    'id' => $teacher->user->id,
                    'name' => $teacher->user->name,
                    'profile_picture_url' => $teacher->profile_picture_url,
                    'bio' => $teacher->bio,
                    'specializations' => $teacher->specializations ?? [],
                    'availability' => $teacher->availability->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'date' => optional($slot->slot_date)->format('Y-m-d'),
                            'time' => $slot->slot_time ? TimeHelper::format($slot->slot_time) : null,
                            'is_booked' => (bool) $slot->is_booked,
                            'booked_by_id' => $slot->booked_by_client_id,
                            'bookedByName' => optional($slot->bookedBy)->name,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $payments = $user->payments()->latest()->take(20)->get();

        $extra = [
            'membership' => $this->formatMembership($membership),
            'membership_payment' => $membership->payment ? $this->formatPayment($membership->payment) : null,
            'payments' => $this->formatPayments($payments),
        ];

        if ($user->role === 'Admin') {
            $autoGenerateMemberships = $this->shouldAutoGenerateMemberships();
            $extra['clients'] = $this->loadClients($autoGenerateMemberships);
            $extra['teacherAdminList'] = $this->loadTeacherAdminList();
            $extra['courseUnpaidSummary'] = $this->loadCourseUnpaidSummary();
            $extra['membershipSummary'] = $this->buildMembershipSummary($extra['clients']);
        } elseif ($user->role === 'Teacher') {
            $extra = array_merge($extra, $this->loadTeacherData($user->id));
        } elseif ($user->role === 'Client') {
            $extra = array_merge($extra, $this->loadClientData($user->id));
        }

        return view('dashboard.index', [
            'user' => $user,
            'courses' => $courses,
            'teachers' => $teachers,
            ...$extra,
        ]);
    }

    private function loadClients(bool $autoGenerate)
    {
        $manager = app(MembershipManager::class);
        $season = $manager->determineCurrentSeason();

        return \App\Models\User::select([
                'id',
                'name',
                'first_name',
                'last_name',
                'email',
                'role',
                'status',
                'email_verified_at',
                'telephone',
                'codice_fiscale',
                'luogo_nascita',
                'data_nascita',
                'residenza_via',
                'residenza_numero_civico',
                'residenza_citta',
                'residenza_provincia',
                'residenza_stato',
            ])
            ->with([
                'membershipSubscriptions' => function ($query) use ($season) {
                    $query->where('season_start_year', $season['start_year']);
                },
                'payments' => function ($query) {
                    $query->latest()->take(15);
                },
            ])
            ->where('role', 'Client')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($manager, $autoGenerate) {
                $current = $manager->ensureCurrentMembership($user, $autoGenerate);
                $user->setAttribute('current_membership', $current);
                $user->setAttribute('membership_payment', $current?->payment);
                $user->setRelation('payments', $user->payments->sortByDesc('created_at')->values());
                return $user;
            })
            ->values();
    }

    private function buildMembershipSummary($clients): array
    {
        $entries = collect($clients)
            ->filter(function ($client) {
                return optional($client->membership_payment)->status === 'pending';
            })
            ->map(function ($client) {
                $payment = $client->membership_payment;
                $membership = $client->current_membership;

                return [
                    'client_id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'telephone' => $client->telephone,
                    'amount' => $payment?->amount ?? $membership?->amount ?? 0,
                    'due_date' => optional($payment?->due_date ?? $membership?->due_date)->format('Y-m-d'),
                    'payment_id' => optional($payment)->id,
                ];
            })
            ->values();

        return [
            'total' => $entries->count(),
            'entries' => $entries,
        ];
    }

    private function shouldAutoGenerateMemberships(): bool
    {
        $setting = Setting::query()->find('membership_auto_generate');

        return isset($setting) ? (bool) $setting->value : false;
    }

    private function loadTeacherAdminList()
    {
        return Teacher::with(['user', 'courses:id,title,teacher_id'])
            ->get()
            ->sortBy(fn ($teacher) => $teacher->user->name)
            ->values();
    }

    private function loadCourseUnpaidSummary(): array
    {
        $now = now();
        $endOfMonth = $now->copy()->endOfMonth();

        $courses = Course::orderBy('title')
            ->get(['id', 'title', 'price']);

        $payments = Payment::query()
            ->where('type', 'course_subscription')
            ->where('status', 'pending')
            ->where('payable_type', Subscription::class)
            ->where(function ($query) use ($endOfMonth) {
                $query->whereDate('due_date', '<=', $endOfMonth)
                    ->orWhereNull('due_date');
            })
            ->get();

        if ($payments->isEmpty()) {
            $courseSummary = $courses->map(function (Course $course) {
                return [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'price' => $course->price,
                    'count' => 0,
                    'unpaid' => [],
                ];
            })->values()->all();

            return [
                'month_label' => $now->translatedFormat('F Y'),
                'total_unpaid' => 0,
                'courses' => $courseSummary,
            ];
        }

        $subscriptionIds = $payments
            ->pluck('payable_id')
            ->filter()
            ->unique()
            ->values();

        $subscriptions = Subscription::with([
                'course:id,title,price',
                'client:id,name,email,telephone',
            ])
            ->whereIn('id', $subscriptionIds)
            ->get()
            ->keyBy('id');

        $courseSummaries = $courses->mapWithKeys(function (Course $course) {
            return [
                $course->id => [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'price' => $course->price,
                    'unpaid' => [],
                ],
            ];
        })->toArray();

        foreach ($payments as $payment) {
            $subscription = $subscriptions->get($payment->payable_id);

            if (!$subscription || !$subscription->course) {
                continue;
            }

            $courseId = $subscription->course_id;

            if (!array_key_exists($courseId, $courseSummaries)) {
                $courseSummaries[$courseId] = [
                    'course_id' => $courseId,
                    'title' => $subscription->course->title,
                    'price' => $subscription->course->price,
                    'unpaid' => [],
                ];
            }

            $courseSummaries[$courseId]['unpaid'][] = [
                'payment_id' => $payment->id,
                'client_id' => $subscription->client_id,
                'client_name' => optional($subscription->client)->name,
                'client_email' => optional($subscription->client)->email,
                'client_telephone' => optional($subscription->client)->telephone,
                'amount' => $payment->amount,
                'due_date' => optional($payment->due_date)->format('Y-m-d'),
                'period_label' => optional($payment->due_date)->translatedFormat('F Y'),
                'created_at' => optional($payment->created_at)->format('Y-m-d H:i'),
            ];
        }

        $courseSummary = collect($courseSummaries)
            ->map(function (array $course) {
                usort($course['unpaid'], function ($a, $b) {
                    $dateA = $a['due_date'] ? \Carbon\Carbon::parse($a['due_date']) : null;
                    $dateB = $b['due_date'] ? \Carbon\Carbon::parse($b['due_date']) : null;

                    if ($dateA && $dateB) {
                        return $dateA->timestamp <=> $dateB->timestamp;
                    }

                    if ($dateA) {
                        return -1;
                    }

                    if ($dateB) {
                        return 1;
                    }

                    return 0;
                });

                $course['count'] = count($course['unpaid']);

                return $course;
            })
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $totalUnpaid = array_sum(array_column($courseSummary, 'count'));

        return [
            'month_label' => $now->translatedFormat('F Y'),
            'total_unpaid' => $totalUnpaid,
            'courses' => $courseSummary,
        ];
    }

    private function loadTeacherData(int $teacherId): array
    {
        $bookings = Booking::with(['availability', 'client'])
            ->where('teacher_id', $teacherId)
            ->orderByDesc('id')
            ->get()
            ->map(function (Booking $booking) {
                $slot = $booking->availability;
                return [
                    'id' => $booking->id,
                    'client' => optional($booking->client)?->only(['id', 'name', 'email', 'status', 'telephone']),
                    'date' => optional($slot->slot_date)->format('Y-m-d'),
                    'time' => $slot?->slot_time ? TimeHelper::format($slot->slot_time) : null,
                ];
            })
            ->values();

        $clients = \App\Models\User::query()
            ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.telephone')
            ->join('bookings', 'users.id', '=', 'bookings.client_id')
            ->where('bookings.teacher_id', $teacherId)
            ->where('users.role', 'Client')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        return [
            'bookings' => $bookings,
            'clients' => $clients,
        ];
    }

    private function loadClientData(int $clientId): array
    {
        $bookings = Booking::with(['availability', 'teacher'])
            ->where('client_id', $clientId)
            ->orderByDesc('id')
            ->get()
            ->map(function (Booking $booking) {
                $slot = $booking->availability;
                return [
                    'id' => $booking->id,
                    'teacher' => optional($booking->teacher)?->only(['id', 'name']),
                    'date' => optional($slot->slot_date)->format('Y-m-d'),
                    'time' => $slot?->slot_time ? TimeHelper::format($slot->slot_time) : null,
                ];
            })
            ->values();

        $subscriptions = Subscription::with('course')
            ->where('client_id', $clientId)
            ->get()
            ->map(function (Subscription $subscription) {
                return [
                    'id' => $subscription->id,
                    'course_id' => $subscription->course_id,
                    'auto_renew' => (bool) $subscription->auto_renew,
                    'course' => optional($subscription->course)?->only(['id', 'title', 'price']),
                ];
            })
            ->values();

        return [
            'bookings' => $bookings,
            'subscriptions' => $subscriptions,
        ];
    }

    private function formatMembership(?MembershipSubscription $membership): ?array
    {
        if (!$membership) {
            return null;
        }

        return [
            'id' => $membership->id,
            'season_start_year' => $membership->season_start_year,
            'starts_at' => optional($membership->starts_at)?->format('Y-m-d'),
            'ends_at' => optional($membership->ends_at)?->format('Y-m-d'),
            'status' => $membership->status,
            'amount' => $membership->amount,
            'due_date' => optional($membership->due_date)?->format('Y-m-d'),
            'paid_at' => optional($membership->paid_at)?->format('Y-m-d H:i'),
        ];
    }

    private function formatPayments($payments)
    {
        return $payments->map(fn (Payment $payment) => $this->formatPayment($payment))->values();
    }

    private function formatPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'type' => $payment->type,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'due_date' => optional($payment->due_date)?->format('Y-m-d'),
            'paid_at' => optional($payment->paid_at)?->format('Y-m-d H:i'),
            'method' => $payment->method,
            'meta' => $payment->meta ?? [],
            'receipt_url' => $payment->receipt_url,
        ];
    }
}
