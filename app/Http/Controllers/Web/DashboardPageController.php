<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Course;
use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\UserDocument;
use App\Models\Teacher;
use App\Services\CoursePaymentGenerator;
use App\Services\MembershipManager;
use App\Support\TimeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class DashboardPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = Auth::user();
        $membershipManager = app(MembershipManager::class);
        $membership = $membershipManager->ensureCurrentMembership($user, false);

        if ($user->role === 'Admin') {
            $this->maybeAutoGenerateCoursePayments();
        }

        $courses = Course::with(['teacher', 'schedule'])
            ->orderBy('title')
            ->get()
            ->map(function (Course $course) {
                $plans = $course->availablePlans();

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'teacher_id' => $course->teacher_id,
                    'teacher_name' => optional($course->teacher)->name,
                    'teacherId' => $course->teacher_id,
                    'teacherName' => optional($course->teacher)->name,
                    'price' => $course->price,
                    'monthly_price' => $course->monthly_price,
                    'quarterly_price' => $course->quarterly_price,
                    'annual_price' => $course->annual_price,
                    'monthlyPrice' => $course->monthly_price,
                    'quarterlyPrice' => $course->quarterly_price,
                    'annualPrice' => $course->annual_price,
                    'allows_extra_day' => (bool) $course->allows_extra_day,
                    'allowsExtraDay' => (bool) $course->allows_extra_day,
                    'extra_day_discount_percent' => $course->extra_day_discount_percent ?? 0,
                    'extraDayDiscountPercent' => $course->extra_day_discount_percent ?? 0,
                    'available_plans' => $plans,
                    'availablePlans' => $plans,
                    'speciality_description' => $course->speciality_description,
                    'specialityDescription' => $course->speciality_description,
                    'gallery' => $course->gallery ?? [],
                    'start_date' => optional($course->start_date)?->format('Y-m-d'),
                    'end_date' => optional($course->end_date)?->format('Y-m-d'),
                    'start_date_human' => optional($course->start_date)?->format('d/m/Y'),
                    'end_date_human' => optional($course->end_date)?->format('d/m/Y'),
                    'startDate' => optional($course->start_date)?->format('Y-m-d'),
                    'endDate' => optional($course->end_date)?->format('Y-m-d'),
                    'startDateHuman' => optional($course->start_date)?->format('d/m/Y'),
                    'endDateHuman' => optional($course->end_date)?->format('d/m/Y'),
                    'schedule' => $course->schedule->map(function ($slot) {
                        return [
                            'day' => $slot->day_of_week,
                            'time' => $slot->time ? TimeHelper::format($slot->time) : null,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $extraDayEnabled = (bool) optional(Setting::find('extra_day_enabled'))->value;
        $privateLessonsEnabled = (bool) optional(Setting::find('private_lessons_enabled'))->value;
        $extraDayCandidates = $extraDayEnabled
            ? $courses->filter(fn ($course) => !empty($course['allows_extra_day']))->pluck('id')->values()->all()
            : [];

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
            $showFuture = (bool) $request->boolean('show_future_course_payments', false);
            $extra['courseUnpaidSummary'] = $this->loadCourseUnpaidSummary($showFuture);
            $extra['courseUnpaidShowFuture'] = $showFuture;
            $extra['membershipSummary'] = $this->buildMembershipSummary($extra['clients'], $request);
        } elseif ($user->role === 'Teacher') {
            $extra = array_merge($extra, $this->loadTeacherData($user->id));
        } elseif ($user->role === 'Client') {
            $extra = array_merge($extra, $this->loadClientData($user->id));
        }

        return view('dashboard.index', [
            'user' => $user,
            'courses' => $courses,
            'teachers' => $teachers,
            'extra_day_config' => [
                'enabled' => $extraDayEnabled,
                'candidateCourseIds' => $extraDayCandidates,
            ],
            'private_lessons_enabled' => $privateLessonsEnabled,
            ...$extra,
        ]);
    }

    private function loadClients(bool $autoGenerate)
    {
        $manager = app(MembershipManager::class);
        $season = $manager->determineCurrentSeason();

        $currentYear = now()->year;

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
                    $query->latest();
                },
                'documents:id,user_id,type,original_name,updated_at,path',
            ])
            ->where('role', 'Client')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($manager, $autoGenerate, $currentYear) {
                $current = $manager->ensureCurrentMembership($user, $autoGenerate);
                $user->setAttribute('current_membership', $current);
                $user->setAttribute('membership_payment', $current?->payment);

                $sortedPayments = $user->payments->sortByDesc('created_at')->values();
                $user->setRelation('payments', $sortedPayments);

                $presentedPayments = $sortedPayments
                    ->map(fn (Payment $payment) => $this->presentAdminPayment($payment))
                    ->values();

                $user->setAttribute('admin_payments_all', $presentedPayments);
                $user->setAttribute(
                    'admin_payments_current_year',
                    $presentedPayments
                        ->filter(fn (array $payment) => ($payment['year'] ?? null) === $currentYear)
                        ->values()
                );

                $documents = $user->documents
                    ->map(function ($document) {
                        $downloadUrl = route('admin.users.documents.download', [$document->user_id, $document->id], false);

                        return [
                            'id' => $document->id,
                            'type' => $document->type,
                            'original_name' => $document->original_name,
                            'originalName' => $document->original_name,
                            'uploaded_at' => optional($document->updated_at)->toIso8601String(),
                            'uploaded_at_display' => optional($document->updated_at)->translatedFormat('d/m/Y H:i'),
                            'uploadedAtDisplay' => optional($document->updated_at)->translatedFormat('d/m/Y H:i'),
                            'url' => Storage::disk('public')->url($document->path),
                            'download_url' => $downloadUrl,
                            'downloadUrl' => $downloadUrl,
                        ];
                    })
                    ->values();

                $requiredDocumentTypes = [
                    'id_front',
                    'id_back',
                    'health_card',
                    'medical_certificate',
                ];

                $missingDocuments = collect($requiredDocumentTypes)
                    ->reject(function ($type) use ($documents) {
                        return $documents->contains(fn ($doc) => $doc['type'] === $type);
                    })
                    ->values();

                $pendingPaymentCount = $presentedPayments->where('status', 'pending')->count();

                $user->setAttribute('admin_documents', $documents);
                $user->setAttribute('admin_missing_documents', $missingDocuments);
                $user->setAttribute('admin_pending_payments_count', $pendingPaymentCount);

                return $user;
            })
            ->values();
    }

    private function buildMembershipSummary($clients, Request $request): array
    {
        $perPageSetting = Setting::query()->find('membership_morosita_page_size');
        $defaultPerPage = config('app.membership_summary_page_size', 5);
        $perPage = (int) ($perPageSetting?->value ?? $defaultPerPage);
        if ($perPage < 1) {
            $perPage = max(1, (int) $defaultPerPage);
        }

        $entries = collect($clients)
            ->filter(function ($client) {
                return optional($client->membership_payment)->status === 'pending';
            })
            ->map(function ($client) {
                $payment = $client->membership_payment;
                $membership = $client->current_membership;
                $seasonStart = $membership?->season_start_year;

                return [
                    'client_id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'telephone' => $client->telephone,
                    'amount' => $payment?->amount ?? $membership?->amount ?? 0,
                    'season_label' => $seasonStart ? (string) $seasonStart : null,
                    'payment_id' => optional($payment)->id,
                ];
            })
            ->values();

        $total = $entries->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = (int) $request->query('membership_page', 1);
        if ($currentPage < 1) {
            $currentPage = 1;
        } elseif ($currentPage > $lastPage) {
            $currentPage = $lastPage;
        }

        $paginated = $entries->forPage($currentPage, $perPage)->values();

        return [
            'total' => $total,
            'entries' => $paginated,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
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

    private function loadCourseUnpaidSummary(bool $includeFuture = false): array
    {
        $now = now();
        $endOfMonth = $now->copy()->endOfMonth();

        $courses = Course::orderBy('title')
            ->get(['id', 'title', 'price', 'monthly_price', 'quarterly_price', 'annual_price']);

        $paymentsQuery = Payment::query()
            ->where('type', 'course_subscription')
            ->where('status', 'pending')
            ->where('payable_type', Subscription::class)
            ->when(!$includeFuture, function ($query) use ($endOfMonth) {
                $query->where(function ($inner) use ($endOfMonth) {
                    $inner->whereDate('due_date', '<=', $endOfMonth)
                        ->orWhereNull('due_date');
                });
            })
            ->when($includeFuture, function ($query) {
                $query->orderByRaw('COALESCE(due_date, "9999-12-31") ASC');
            });

        $payments = $paymentsQuery->get();

        if ($payments->isEmpty()) {
            $courseSummary = $courses->map(function (Course $course) {
                return [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'price' => $course->price,
                    'count' => 0,
                    'future_count' => 0,
                    'unpaid' => [],
                ];
            })->values()->all();

            return [
                'month_label' => $includeFuture ? __('tutte le scadenze') : $now->translatedFormat('F Y'),
                'total_unpaid' => 0,
                'future_total' => 0,
                'showing_future' => $includeFuture,
                'courses' => $courseSummary,
            ];
        }

        $subscriptionIds = $payments
            ->pluck('payable_id')
            ->filter()
            ->unique()
            ->values();

        $subscriptionQuery = Subscription::with([
                'course:id,title,price,monthly_price,quarterly_price,annual_price',
                'client:id,name,email,telephone,status',
            ])
            ->whereIn('id', $subscriptionIds);

        if (Schema::hasColumn('subscriptions', 'status')) {
            $subscriptionQuery->where('status', 'active');
        }

        $subscriptions = $subscriptionQuery
            ->get()
            ->keyBy('id');

        $courseSummaries = $courses->mapWithKeys(function (Course $course) {
            $plans = $course->availablePlans();
            $referencePrice = $course->monthly_price ?? $course->price;

            return [
                $course->id => [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'price' => $referencePrice,
                    'plans' => $plans,
                    'unpaid' => [],
                    'future_count' => 0,
                ],
            ];
        })->toArray();

        foreach ($payments as $payment) {
            $subscription = $subscriptions->get($payment->payable_id);

            if (!$subscription || !$subscription->course) {
                continue;
            }

            if (optional($subscription->client)->status === 'disabled') {
                continue;
            }

            $courseId = $subscription->course_id;

            if (!array_key_exists($courseId, $courseSummaries)) {
                $courseSummaries[$courseId] = [
                    'course_id' => $courseId,
                    'title' => $subscription->course->title,
                    'price' => $subscription->plan_amount,
                    'plans' => $subscription->course?->availablePlans() ?? [],
                    'unpaid' => [],
                    'future_count' => 0,
                ];
            }

            $isFuture = $includeFuture && optional($payment->due_date)->greaterThan($endOfMonth);

            if ($isFuture) {
                $courseSummaries[$courseId]['future_count'] = ($courseSummaries[$courseId]['future_count'] ?? 0) + 1;
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
                'plan_type' => $subscription->plan_type,
                'plan_label' => $subscription->plan_label,
                'plan_amount' => $subscription->plan_amount,
                'is_future' => $isFuture,
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
        $futureTotal = array_sum(array_map(fn ($course) => $course['future_count'] ?? 0, $courseSummary));

        return [
            'month_label' => $includeFuture ? __('tutte le scadenze') : $now->translatedFormat('F Y'),
            'total_unpaid' => $totalUnpaid,
            'future_total' => $futureTotal,
            'showing_future' => $includeFuture,
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

        $receiptPayments = Payment::with(['user:id,name,email', 'payable'])
            ->where('payable_type', Booking::class)
            ->whereNotNull('receipt_path')
            ->whereHasMorph('payable', [Booking::class], function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get()
            ->map(function (Payment $payment) {
                $booking = $payment->payable;
                $slot = $booking?->availability;

                return [
                    'id' => $payment->id,
                    'client_name' => optional($payment->user)->name,
                    'client_email' => optional($payment->user)->email,
                    'amount' => $payment->amount,
                    'amount_formatted' => number_format((float) $payment->amount, 2, ',', '.'),
                    'paid_at' => optional($payment->paid_at)->format('d/m/Y H:i'),
                    'lesson_date' => optional($slot?->slot_date)->format('d/m/Y'),
                    'lesson_time' => $slot?->slot_time ? TimeHelper::format($slot->slot_time) : null,
                    'receipt_route' => $payment->receipt_url ? route('payments.receipt', $payment->id) : null,
                ];
            })
            ->values();

        return [
            'bookings' => $bookings,
            'clients' => $clients,
            'receipts' => $receiptPayments,
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

        $subscriptions = Subscription::with(['course', 'extraCourse'])
            ->where('client_id', $clientId)
            ->get()
            ->map(function (Subscription $subscription) {
                $courseData = optional($subscription->course)?->only([
                    'id',
                    'title',
                    'price',
                    'monthly_price',
                    'quarterly_price',
                    'annual_price',
                ]);

                return [
                    'id' => $subscription->id,
                    'course_id' => $subscription->course_id,
                    'courseId' => $subscription->course_id,
                    'client_id' => $subscription->client_id,
                    'clientId' => $subscription->client_id,
                    'auto_renew' => (bool) $subscription->auto_renew,
                    'autoRenew' => (bool) $subscription->auto_renew,
                    'start_date' => optional($subscription->start_date)?->format('Y-m-d'),
                    'startDate' => optional($subscription->start_date)?->format('Y-m-d'),
                    'startDateDisplay' => optional($subscription->start_date)?->translatedFormat('d/m/Y'),
                    'end_date' => optional($subscription->end_date)?->format('Y-m-d'),
                    'endDate' => optional($subscription->end_date)?->format('Y-m-d'),
                    'endDateDisplay' => optional($subscription->end_date)?->translatedFormat('d/m/Y'),
                    'plan_type' => $subscription->plan_type,
                    'planType' => $subscription->plan_type,
                    'plan_label' => $subscription->plan_label,
                    'planLabel' => $subscription->plan_label,
                    'plan_amount' => $subscription->plan_amount,
                    'planAmount' => $subscription->plan_amount,
                    'status' => $subscription->status,
                    'cancelled_at' => optional($subscription->cancelled_at)?->toIso8601String(),
                    'cancelledAt' => optional($subscription->cancelled_at)?->toIso8601String(),
                    'cancelledAtDisplay' => optional($subscription->cancelled_at)?->translatedFormat('d/m/Y H:i'),
                    'course' => $courseData,
                    'extra_course_id' => $subscription->extra_course_id,
                    'extraCourseId' => $subscription->extra_course_id,
                    'extra_course_plan_amount' => $subscription->extra_course_plan_amount,
                    'extraCoursePlanAmount' => $subscription->extra_course_plan_amount,
                    'extra_course_snapshot' => $subscription->extra_course_snapshot,
                    'extraCourseSnapshot' => $subscription->extra_course_snapshot,
                    'extra_course' => optional($subscription->extraCourse)?->only([
                        'id',
                        'title',
                        'monthly_price',
                        'teacher_id',
                    ]),
                    'hasExtraDay' => $subscription->hasExtraDay(),
                ];
            })
            ->values();

        $documents = UserDocument::where('user_id', $clientId)
            ->orderBy('type')
            ->get()
            ->map(function (UserDocument $document) {
                return [
                    'id' => $document->id,
                    'type' => $document->type,
                    'original_name' => $document->original_name,
                    'originalName' => $document->original_name,
                    'url' => Storage::disk('public')->url($document->path),
                    'download_url' => route('admin.users.documents.download', [$document->user_id, $document->id], false),
                    'downloadUrl' => route('admin.users.documents.download', [$document->user_id, $document->id], false),
                    'uploaded_at' => optional($document->updated_at)->toIso8601String(),
                    'uploadedAtDisplay' => optional($document->updated_at)->translatedFormat('d/m/Y H:i'),
                ];
            });

        return [
            'bookings' => $bookings,
            'subscriptions' => $subscriptions,
            'documents' => $documents,
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
        $meta = $payment->meta ?? [];
        $courseTitle = $payment->course?->title ?? ($meta['course_title'] ?? null);
        $planLabel = $meta['plan_label'] ?? null;
        $planType = $meta['plan_type'] ?? null;
        $startDate = $meta['start_date'] ?? $meta['renewal_cycle_start'] ?? optional($payment->due_date)?->format('Y-m-d');
        $startDateDisplay = $startDate ? Carbon::parse($startDate)->translatedFormat('d/m/Y') : null;
        $extraDay = $meta['extra_day'] ?? null;

        return [
            'id' => $payment->id,
            'type' => $payment->type,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'due_date' => optional($payment->due_date)?->format('Y-m-d'),
            'paid_at' => optional($payment->paid_at)?->format('Y-m-d H:i'),
            'method' => $payment->method,
            'meta' => $meta,
            'plan_type' => $planType,
            'plan_label' => $planLabel,
            'course_id' => $payment->course_id,
            'course_title' => $courseTitle,
            'courseTitle' => $courseTitle,
            'planLabel' => $planLabel,
            'planType' => $planType,
            'subscription_start_date' => $startDate,
            'subscriptionStartDate' => $startDate,
            'subscriptionStartDateDisplay' => $startDateDisplay,
            'extra_day' => $extraDay,
            'has_extra_day' => !empty($extraDay),
            'receipt_url' => $payment->receipt_url,
            'receipt_route' => $payment->receipt_url ? route('payments.receipt', $payment->id) : null,
            'is_course_payment' => $payment->type === 'course_subscription',
        ];
    }

    private function presentAdminPayment(Payment $payment): array
    {
        $typeLabels = [
            'membership' => 'Quota associativa',
            'course_subscription' => 'Iscrizione corso',
            'private_lesson' => 'Lezione privata',
        ];

        $statusLabels = [
            'pending' => 'In attesa',
            'paid' => 'Pagato',
            'waived' => 'Annullato',
        ];

        $statusBadgeClass = match ($payment->status) {
            'paid' => 'bg-emerald-100 text-emerald-600',
            'waived' => 'bg-sky-100 text-sky-600',
            default => 'bg-amber-100 text-amber-600',
        };

        $createdAt = $payment->created_at;
        $dueDate = $payment->due_date;

        $meta = $payment->meta ?? [];
        $planLabel = $meta['plan_label'] ?? null;
        $planType = $meta['plan_type'] ?? null;

        $typeLabel = $typeLabels[$payment->type] ?? ucfirst(str_replace('_', ' ', $payment->type));
        if ($payment->type === 'course_subscription' && $planLabel) {
            $typeLabel .= ' · ' . $planLabel;
        }

        return [
            'id' => $payment->id,
            'type' => $payment->type,
            'type_label' => $typeLabel,
            'status' => $payment->status,
            'status_label' => $statusLabels[$payment->status] ?? ucfirst($payment->status),
            'status_badge_class' => $statusBadgeClass,
            'amount' => (float) $payment->amount,
            'amount_formatted' => number_format((float) $payment->amount, 2, ',', '.'),
            'due_date' => optional($dueDate)?->format('Y-m-d'),
            'due_date_display' => optional($dueDate)?->format('d/m/Y'),
            'created_at' => optional($createdAt)?->toIso8601String(),
            'created_at_display' => optional($createdAt)?->format('d/m/Y H:i'),
            'year' => optional($createdAt)?->year,
            'plan_type' => $planType,
            'plan_label' => $planLabel,
            'routes' => [
                'update' => route('admin.payments.update', $payment->id),
                'receipt' => $payment->receipt_url ? route('admin.payments.receipt', $payment->id) : null,
                'reprint' => $payment->receipt_url ? route('admin.payments.reprint', $payment->id) : null,
            ],
            'receipt_available' => (bool) $payment->receipt_url,
            'receipt_route' => $payment->receipt_url ? route('admin.payments.receipt', $payment->id) : null,
            'is_pending' => $payment->status === 'pending',
            'is_course' => $payment->type === 'course_subscription',
        ];
    }

    private function maybeAutoGenerateCoursePayments(): void
    {
        $autoSetting = Setting::query()->find('course_payment_auto_generate');
        if (!$autoSetting || !$autoSetting->value) {
            return;
        }

        $leadSetting = Setting::query()->find('course_payment_lead_days');
        $leadDays = $leadSetting ? (int) $leadSetting->value : 10;

        $generator = app(CoursePaymentGenerator::class);
        $result = $generator->generate($leadDays);

        $payload = array_merge($result, [
            'manual' => false,
            'timestamp' => $result['run_at'] ?? now()->toDateTimeString(),
        ]);

        Setting::updateOrCreate(['key' => 'course_payment_last_run'], [
            'value' => json_encode($payload),
        ]);
    }
}
