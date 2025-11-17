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
use Illuminate\Support\Collection;

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

        $courses = Course::with([
                'teacher',
                'schedule',
                'subscriptions.client',
                'subscriptions.payments' => function ($query) {
                    $query->where('type', 'course_subscription');
                },
                'subscriptions.lessons',
            ])
            ->orderBy('title')
            ->get()
            ->map(function (Course $course) {
                $plans = $course->availablePlans();
                $activeSubscriptions = $course->subscriptions
                    ? $course->subscriptions->filter(fn ($subscription) => $subscription->status !== 'cancelled')
                    : collect();
                $lessonUsage = $activeSubscriptions
                    ->flatMap(function ($subscription) {
                        return $subscription->lessons ? $subscription->lessons->pluck('course_schedule_id') : collect();
                    })
                    ->filter()
                    ->countBy();
                $enrollmentCount = $activeSubscriptions->count();
                $courseFull = $course->max_enrollments && $enrollmentCount >= $course->max_enrollments;

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
                    'pricing_mode' => $course->pricing_mode ?? 'block',
                    'pricingMode' => $course->pricing_mode ?? 'block',
                    'max_enrollments' => $course->max_enrollments,
                    'maxEnrollments' => $course->max_enrollments,
                    'lesson_pricing' => $course->lesson_pricing ?? [],
                    'lessonPricing' => $course->lesson_pricing ?? [],
                    'enrollment_count' => $enrollmentCount,
                    'enrollmentCount' => $enrollmentCount,
                    'enrollment_full' => (bool) $courseFull,
                    'enrollmentFull' => (bool) $courseFull,
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
                    'schedule' => $course->schedule->map(function ($slot) use ($lessonUsage) {
                        $used = (int) ($lessonUsage[$slot->id] ?? 0);
                        $capacity = $slot->capacity;
                        $available = is_null($capacity) ? null : max($capacity - $used, 0);
                        $label = trim($slot->day_of_week . ' ' . ($slot->time ? TimeHelper::format($slot->time) : ''));

                        return [
                            'id' => $slot->id,
                            'day' => $slot->day_of_week,
                            'time' => $slot->time ? TimeHelper::format($slot->time) : null,
                            'capacity' => $capacity,
                            'used' => $used,
                            'available' => $available,
                            'full' => $capacity !== null && $used >= $capacity,
                            'label' => $label,
                        ];
                    })->values(),
                    'students' => $this->mapCourseStudents($course),
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
                    'can_manage_courses' => (bool) $teacher->can_manage_courses,
                    'can_manage_payments' => (bool) $teacher->can_manage_payments,
                    'can_manage_students' => (bool) $teacher->can_manage_students,
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

        $dashboardViewConfig = [
            'mode' => 'admin',
            'show_membership_panel' => true,
            'show_course_unpaid' => true,
            'show_client_admin' => true,
            'show_teacher_admin' => true,
            'show_course_admin' => true,
            'allow_course_creation' => true,
            'allow_teacher_selection' => true,
            'allow_student_manage' => true,
            'show_settings' => true,
            'show_private_lessons' => $privateLessonsEnabled,
        ];
        $dashboardStats = null;

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
            $teacherData = $this->loadTeacherData($user->id);
            $courses = collect($teacherData['teacher_courses'] ?? []);
            $teacherProfile = $teacherData['teacher_profile'] ?? [];
            $canManageCourses = (bool) ($teacherProfile['can_manage_courses'] ?? false);
            $canManagePayments = (bool) ($teacherProfile['can_manage_payments'] ?? false);

            $extra['clients'] = collect();
            $extra['teacherAdminList'] = collect([$user->teacherProfile])->filter();
            $teacherShowFuture = $canManagePayments ? (bool) $request->boolean('show_future_course_payments', false) : false;
            $extra['courseUnpaidSummary'] = $canManagePayments
                ? $this->buildTeacherCourseUnpaidSummary(
                    collect($teacherData['teacher_courses'] ?? []),
                    collect($teacherData['teacher_course_payments'] ?? []),
                    $teacherShowFuture
                )
                : null;
            $extra['courseUnpaidShowFuture'] = $teacherShowFuture;
            $extra['membershipSummary'] = $canManagePayments
                ? $this->buildTeacherMembershipSummary(collect($teacherData['teacher_membership_payments'] ?? []))
                : null;

            $dashboardViewConfig = array_merge($dashboardViewConfig, [
                'mode' => 'teacher',
                'show_membership_panel' => $canManagePayments,
                'show_course_unpaid' => $canManagePayments,
                'show_client_admin' => (bool) ($teacherProfile['can_manage_students'] ?? false),
                'show_teacher_admin' => false,
                'show_course_admin' => $canManageCourses,
                'allow_course_creation' => false,
                'allow_teacher_selection' => false,
                'allow_student_manage' => (bool) ($teacherProfile['can_manage_students'] ?? false),
                'show_settings' => false,
                'course_card_title' => 'I miei corsi',
                'course_card_subtitle' => 'Puoi modificare e aggiornare solo i corsi assegnati.',
                'current_teacher_id' => $user->id,
                'show_private_lessons' => ($teacherProfile['can_host_private'] ?? false) && $privateLessonsEnabled,
            ]);

            $dashboardStats = [
                'clients' => collect($teacherData['teacher_students'] ?? [])->count(),
                'courses' => $courses->count(),
                'teachers' => 1,
            ];

            $extra['bookings'] = $teacherData['bookings'] ?? [];
            $extra['clients'] = $extra['clients'];
            $extra['receipts'] = $teacherData['receipts'] ?? [];
            $courses = $courses;
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
            'dashboardViewConfig' => $dashboardViewConfig,
            'dashboardStats' => $dashboardStats,
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
        // Paginazione allineata alla contabilità, con default 2 e opzioni consentite.
        $perPage = (int) $request->get('per_page', 2);
        if (!in_array($perPage, [2, 25, 50, 100], true)) {
            $perPage = 2;
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

    private function buildTeacherCourseUnpaidSummary(\Illuminate\Support\Collection $courses, \Illuminate\Support\Collection $payments, bool $includeFuture = false): array
    {
        $now = now();
        $endOfMonth = $now->copy()->endOfMonth();

        $courseSummaries = $courses->mapWithKeys(function ($course) {
            $courseId = $course['id'] ?? null;
            if (!$courseId) {
                return [];
            }

            $plans = $course['availablePlans'] ?? $course['available_plans'] ?? [];
            $referencePrice = $course['monthly_price'] ?? $course['price'] ?? 0;

            return [
                $courseId => [
                    'course_id' => $courseId,
                    'title' => $course['title'] ?? __('Corso'),
                    'price' => $referencePrice,
                    'plans' => $plans,
                    'unpaid' => [],
                    'future_count' => 0,
                ],
            ];
        })->all();

        $pendingPayments = $payments->filter(function ($payment) {
            return ($payment['status'] ?? null) === 'pending';
        });

        if (!$includeFuture) {
            $pendingPayments = $pendingPayments->filter(function ($payment) use ($endOfMonth) {
                if (empty($payment['due_date'])) {
                    return true;
                }

                $dueDate = Carbon::parse($payment['due_date']);

                return $dueDate->lessThanOrEqualTo($endOfMonth);
            });
        }

        foreach ($pendingPayments as $payment) {
            $courseId = $payment['course_id'] ?? null;
            if (!$courseId) {
                continue;
            }

            if (!isset($courseSummaries[$courseId])) {
                $courseSummaries[$courseId] = [
                    'course_id' => $courseId,
                    'title' => $payment['course_title'] ?? __('Corso'),
                    'price' => $payment['amount'] ?? 0,
                    'plans' => [],
                    'unpaid' => [],
                    'future_count' => 0,
                ];
            }

            $dueDate = !empty($payment['due_date']) ? Carbon::parse($payment['due_date']) : null;
            $isFuture = $dueDate ? $dueDate->greaterThan($endOfMonth) : false;

            if ($isFuture) {
                $courseSummaries[$courseId]['future_count']++;
            }

            $courseSummaries[$courseId]['unpaid'][] = [
                'payment_id' => $payment['id'] ?? null,
                'client_id' => $payment['user_id'] ?? null,
                'client_name' => $payment['user_name'] ?? null,
                'client_email' => $payment['user_email'] ?? null,
                'client_telephone' => $payment['user_telephone'] ?? null,
                'amount' => $payment['amount'] ?? 0,
                'due_date' => $payment['due_date'] ?? null,
                'period_label' => $dueDate ? $dueDate->translatedFormat('F Y') : null,
                'plan_label' => $payment['plan_label'] ?? null,
                'plan_type' => $payment['plan_type'] ?? null,
                'plan_amount' => $payment['plan_amount'] ?? $payment['amount'] ?? 0,
                'is_future' => $isFuture,
            ];
        }

        if (empty($courseSummaries)) {
            return [
                'month_label' => $includeFuture ? __('tutte le scadenze') : $now->translatedFormat('F Y'),
                'total_unpaid' => 0,
                'future_total' => 0,
                'showing_future' => $includeFuture,
                'courses' => [],
            ];
        }

        $courseSummary = collect($courseSummaries)
            ->map(function (array $course) {
                usort($course['unpaid'], function ($a, $b) {
                    $dateA = !empty($a['due_date']) ? Carbon::parse($a['due_date']) : null;
                    $dateB = !empty($b['due_date']) ? Carbon::parse($b['due_date']) : null;

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

    private function buildTeacherMembershipSummary(\Illuminate\Support\Collection $payments): array
    {
        $pending = $payments->filter(fn ($payment) => ($payment['status'] ?? null) === 'pending');

        $entries = $pending->map(function (array $payment) {
            $seasonLabel = $payment['season_label'] ?? null;
            if (!$seasonLabel && !empty($payment['due_date'])) {
                $seasonLabel = Carbon::parse($payment['due_date'])->format('Y');
            }

            return [
                'client_id' => $payment['user_id'] ?? null,
                'name' => $payment['user_name'] ?? __('Allieva/o'),
                'email' => $payment['user_email'] ?? null,
                'telephone' => $payment['user_telephone'] ?? null,
                'amount' => $payment['amount'] ?? 0,
                'season_label' => $seasonLabel,
                'payment_id' => $payment['id'] ?? null,
            ];
        })->values();

        $total = $entries->count();
        $perPage = max(1, $total ?: 1);

        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => 1,
            'last_page' => 1,
            'entries' => $entries->all(),
        ];
    }

    private function loadTeacherData(int $teacherId): array
    {
        $teacher = Teacher::with([
            'user',
            'courses.schedule',
            'courses.subscriptions.client.documents',
            'courses.subscriptions.payments' => function ($query) {
                $query->where('type', 'course_subscription');
            },
            'courses.subscriptions.lessons',
        ])->where('user_id', $teacherId)->first();

        $courseCollection = collect($teacher?->courses ?? []);

        $courseData = $courseCollection->map(function (Course $course) {
            return $this->formatTeacherCourse($course);
        });

        $studentIds = $courseCollection
            ->flatMap(function (Course $course) {
                return $course->subscriptions->pluck('client_id');
            })
            ->filter()
            ->unique()
            ->values();

        $courseIds = $courseCollection->pluck('id')->filter()->unique()->values();

        $coursePayments = $this->loadTeacherCoursePayments($courseIds);
        $membershipPayments = $this->loadTeacherMembershipPayments($studentIds);
        $students = $this->formatTeacherStudents($teacher, $membershipPayments);

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
            'teacher_profile' => [
                'can_manage_courses' => (bool) optional($teacher)->can_manage_courses,
                'can_manage_payments' => (bool) optional($teacher)->can_manage_payments,
                'can_manage_students' => (bool) optional($teacher)->can_manage_students,
            ],
            'teacher_courses' => $courseData->values()->all(),
            'teacher_students' => $students,
            'teacher_course_payments' => $coursePayments,
            'teacher_membership_payments' => $membershipPayments,
            'bookings' => $bookings,
            'clients' => $clients,
            'receipts' => $receiptPayments,
        ];
    }

    private function formatTeacherCourse(Course $course): array
    {
        $plans = $course->availablePlans();
        $activeSubscriptions = $course->subscriptions
            ? $course->subscriptions->filter(fn ($subscription) => $subscription->status !== 'cancelled')
            : collect();
        $lessonUsage = $activeSubscriptions
            ->flatMap(function ($subscription) {
                return $subscription->lessons ? $subscription->lessons->pluck('course_schedule_id') : collect();
            })
            ->filter()
            ->countBy();
        $enrollmentCount = $activeSubscriptions->count();
        $courseFull = $course->max_enrollments && $enrollmentCount >= $course->max_enrollments;

        return [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'teacher_id' => $course->teacher_id,
            'price' => $course->price,
            'monthly_price' => $course->monthly_price,
            'quarterly_price' => $course->quarterly_price,
            'annual_price' => $course->annual_price,
            'pricing_mode' => $course->pricing_mode ?? 'block',
            'pricingMode' => $course->pricing_mode ?? 'block',
            'max_enrollments' => $course->max_enrollments,
            'maxEnrollments' => $course->max_enrollments,
            'lesson_pricing' => $course->lesson_pricing ?? [],
            'lessonPricing' => $course->lesson_pricing ?? [],
            'enrollment_count' => $enrollmentCount,
            'enrollmentCount' => $enrollmentCount,
            'enrollment_full' => (bool) $courseFull,
            'enrollmentFull' => (bool) $courseFull,
            'monthlyPrice' => $course->monthly_price,
            'quarterlyPrice' => $course->quarterly_price,
            'annualPrice' => $course->annual_price,
            'allows_extra_day' => (bool) $course->allows_extra_day,
            'extra_day_discount_percent' => $course->extra_day_discount_percent ?? 0,
            'start_date' => optional($course->start_date)?->format('Y-m-d'),
            'end_date' => optional($course->end_date)?->format('Y-m-d'),
            'startDateHuman' => optional($course->start_date)?->translatedFormat('d/m/Y'),
            'endDateHuman' => optional($course->end_date)?->translatedFormat('d/m/Y'),
            'speciality_description' => $course->speciality_description,
            'availablePlans' => $plans,
            'schedule' => $course->schedule->map(function ($slot) use ($lessonUsage) {
                $used = (int) ($lessonUsage[$slot->id] ?? 0);
                $capacity = $slot->capacity;
                $available = is_null($capacity) ? null : max($capacity - $used, 0);
                $label = trim($slot->day_of_week . ' ' . ($slot->time ? TimeHelper::format($slot->time) : ''));

                return [
                    'id' => $slot->id,
                    'day' => $slot->day_of_week,
                    'time' => $slot->time ? TimeHelper::format($slot->time) : null,
                    'capacity' => $capacity,
                    'used' => $used,
                    'available' => $available,
                    'full' => $capacity !== null && $used >= $capacity,
                    'label' => $label,
                ];
            })->values(),
            'students' => $this->mapCourseStudents($course),
        ];
    }

    private function loadTeacherCoursePayments(Collection $courseIds): array
    {
        if ($courseIds->isEmpty()) {
            return [];
        }

        return Payment::with('user:id,name,email,telephone')
            ->where('type', 'course_subscription')
            ->whereIn('course_id', $courseIds)
            ->orderByDesc('due_date')
            ->limit(50)
            ->get()
            ->map(function (Payment $payment) {
                $meta = $payment->meta ?? [];

                return [
                    'id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'user_name' => optional($payment->user)->name,
                    'user_email' => optional($payment->user)->email,
                    'user_telephone' => optional($payment->user)->telephone,
                    'course_id' => $payment->course_id,
                    'course_title' => $meta['course_title'] ?? null,
                    'amount' => $payment->amount,
                    'amount_formatted' => number_format((float) $payment->amount, 2, ',', '.'),
                    'status' => $payment->status,
                    'status_badge' => $this->paymentStatusBadge($payment->status),
                    'due_date' => optional($payment->due_date)->format('Y-m-d'),
                    'due_date_display' => optional($payment->due_date)->translatedFormat('d/m/Y'),
                    'plan_label' => $meta['plan_label'] ?? null,
                    'plan_type' => $meta['plan_type'] ?? null,
                    'plan_amount' => $meta['plan_amount'] ?? $payment->amount,
                    'receipt_url' => $payment->receipt_url ? route('payments.receipt', $payment->id) : null,
                ];
            })
            ->values()
            ->all();
    }

    private function loadTeacherMembershipPayments(Collection $studentIds): array
    {
        if ($studentIds->isEmpty()) {
            return [];
        }

        return Payment::with('user:id,name,email,telephone')
            ->where('type', 'membership')
            ->whereIn('user_id', $studentIds)
            ->orderByDesc('due_date')
            ->limit(50)
            ->get()
            ->map(function (Payment $payment) {
                $meta = $payment->meta ?? [];
                $seasonLabel = $meta['season_label']
                    ?? ($meta['season_start_year'] ?? null)
                    ?? $payment->receipt_year;

                return [
                    'id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'user_name' => optional($payment->user)->name,
                    'user_email' => optional($payment->user)->email,
                    'user_telephone' => optional($payment->user)->telephone,
                    'amount' => $payment->amount,
                    'amount_formatted' => number_format((float) $payment->amount, 2, ',', '.'),
                    'status' => $payment->status,
                    'status_badge' => $this->paymentStatusBadge($payment->status),
                    'due_date' => optional($payment->due_date)->format('Y-m-d'),
                    'due_date_display' => optional($payment->due_date)->translatedFormat('d/m/Y'),
                    'season_label' => $seasonLabel,
                    'receipt_url' => $payment->receipt_url ? route('payments.receipt', $payment->id) : null,
                ];
            })
            ->values()
            ->all();
    }

    private function formatTeacherStudents(?Teacher $teacher, array $membershipPayments): array
    {
        if (!$teacher) {
            return [];
        }

        $students = [];
        $membershipPendingMap = collect($membershipPayments)
            ->where('status', 'pending')
            ->mapWithKeys(function ($entry) {
                return [$entry['user_id'] => $entry['due_date_display'] ?? null];
            });

        $requiredDocs = ['id_front', 'id_back', 'health_card', 'medical_certificate'];
        $now = now();
        $currentStart = $now->copy()->startOfMonth();
        $currentEnd = $now->copy()->endOfMonth();
        $nextStart = $currentStart->copy()->addMonth();
        $nextEnd = $nextStart->copy()->endOfMonth();

        foreach ($teacher->courses as $course) {
            foreach ($course->subscriptions as $subscription) {
                $client = $subscription->client;
                if (!$client) {
                    continue;
                }

                if (!isset($students[$client->id])) {
                    $documents = $client->documents ?? collect();
                    $docTypes = $documents->pluck('type')->all();
                    $missing = collect($requiredDocs)
                        ->reject(fn ($type) => in_array($type, $docTypes, true))
                        ->values();

                    $students[$client->id] = [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email,
                        'telephone' => $client->telephone,
                        'whatsapp' => $this->formatWhatsappLink($client->telephone),
                        'status' => ucfirst($client->status),
                        'membership_pending' => $membershipPendingMap->has($client->id),
                        'missing_documents' => $missing->count(),
                        'courses' => [],
                    ];
                }

                $status = $this->determineCourseStudentStatus(
                    $subscription,
                    $currentStart,
                    $currentEnd,
                    $nextStart,
                    $nextEnd
                );

                $students[$client->id]['courses'][] = [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'plan' => $subscription->plan_label,
                    'status' => $status['label'],
                    'badge' => $status['badge'],
                ];
            }
        }

        return collect($students)
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function paymentStatusBadge(string $status): string
    {
        return match ($status) {
            'paid' => 'bg-emerald-100 text-emerald-700',
            'waived' => 'bg-stone-200 text-stone-600',
            default => 'bg-amber-100 text-amber-700',
        };
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

        $subscriptions = Subscription::with(['course', 'extraCourse', 'lessons.schedule'])
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
                    'lessons' => $subscription->lessons
                        ? $subscription->lessons->map(function ($lesson) {
                            $schedule = $lesson->schedule;
                            $day = $schedule->day_of_week ?? null;
                            $timeValue = $schedule->time ?? $lesson->time;
                            $time = $timeValue ? TimeHelper::format($timeValue) : null;
                            return [
                                'course_schedule_id' => $lesson->course_schedule_id,
                                'day' => $day,
                                'time' => $time,
                                'label' => trim(($day ?? '') . ' ' . ($time ?? '')),
                            ];
                        })->values()->all()
                        : [],
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

    private function mapCourseStudents(Course $course): array
    {
        $subscriptions = $course->subscriptions ?? collect();
        if ($subscriptions->isEmpty()) {
            return [];
        }

        $now = now();
        $currentStart = $now->copy()->startOfMonth();
        $currentEnd = $now->copy()->endOfMonth();
        $nextStart = $currentStart->copy()->addMonth();
        $nextEnd = $nextStart->copy()->endOfMonth();

        return $subscriptions
            ->map(function (Subscription $subscription) use ($currentStart, $currentEnd, $nextStart, $nextEnd) {
                $client = $subscription->client;
                if (!$client) {
                    return null;
                }

                $status = $this->determineCourseStudentStatus($subscription, $currentStart, $currentEnd, $nextStart, $nextEnd);

                return [
                    'subscription_id' => $subscription->id,
                    'client_id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'telephone' => $client->telephone,
                    'whatsapp' => $this->formatWhatsappLink($client->telephone),
                    'plan' => $subscription->plan_label,
                    'status' => $status['label'],
                    'status_badge' => $status['badge'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function determineCourseStudentStatus(Subscription $subscription, Carbon $currentStart, Carbon $currentEnd, Carbon $nextStart, Carbon $nextEnd): array
    {
        if ($subscription->status === 'cancelled') {
            return [
                'label' => 'Cancellata',
                'badge' => 'bg-rose-100 text-rose-700',
            ];
        }

        $payments = $subscription->payments ?? collect();

        $pendingCurrent = $payments->first(function ($payment) use ($currentStart, $currentEnd) {
            $due = $payment->due_date;
            return $payment->status === 'pending'
                && $due
                && $due->betweenIncluded($currentStart, $currentEnd);
        });

        if ($pendingCurrent) {
            return [
                'label' => 'In attesa di pagamento',
                'badge' => 'bg-amber-100 text-amber-700',
            ];
        }

        $pendingNext = $payments->first(function ($payment) use ($nextStart, $nextEnd) {
            $due = $payment->due_date;
            return $payment->status === 'pending'
                && $due
                && $due->betweenIncluded($nextStart, $nextEnd);
        });

        if ($pendingNext) {
            return [
                'label' => 'Pendenza prossimo mese',
                'badge' => 'bg-sky-100 text-sky-700',
            ];
        }

        return [
            'label' => 'Regolare',
            'badge' => 'bg-emerald-100 text-emerald-700',
        ];
    }

    private function formatWhatsappLink(?string $telephone): ?string
    {
        if (!$telephone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $telephone);

        return $digits ? 'https://wa.me/' . $digits : null;
    }
}
