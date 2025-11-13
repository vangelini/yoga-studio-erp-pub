<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Services\MembershipManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminClientPageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'Admin';
        $isTeacher = $user?->role === 'Teacher';
        $teacherProfile = $isTeacher ? $user?->teacherProfile : null;
        $teacherCanManageStudents = $isTeacher && $teacherProfile && $teacherProfile->can_manage_students;

        if (!$isAdmin && !$teacherCanManageStudents) {
            abort(403);
        }

        $autoGenerate = $isAdmin ? $this->shouldAutoGenerateMemberships() : false;
        $restrictedClientIds = null;

        if ($teacherCanManageStudents) {
            $restrictedClientIds = $this->getTeacherStudentIds($user->id);

            if (empty($restrictedClientIds)) {
                $clients = collect();
            }
        }

        $clients = $clients ?? $this->loadClients($autoGenerate, $restrictedClientIds);

        $canViewMembershipSummary = $isAdmin || ($teacherProfile?->can_manage_payments ?? false);
        $membershipSummary = $canViewMembershipSummary ? $this->buildMembershipSummary($clients, $request) : null;

        $phonePrefixes = [
            ['code' => '+39', 'name' => 'Italia'],
            ['code' => '+33', 'name' => 'Francia'],
            ['code' => '+49', 'name' => 'Germania'],
            ['code' => '+34', 'name' => 'Spagna'],
            ['code' => '+44', 'name' => 'Regno Unito'],
            ['code' => '+1', 'name' => 'Stati Uniti'],
        ];

        $documentDefinitions = [
            'id_front' => "CI - fronte",
            'id_back' => "CI - retro",
            'health_card' => 'Tessera sanitaria',
            'medical_certificate' => 'Certificato medico',
        ];

        $showFutureCourses = $request->boolean('show_future_course_payments');

        $clientPagePermissions = [
            'mode' => $isAdmin ? 'admin' : 'teacher',
            'can_create' => $isAdmin,
            'can_export' => $isAdmin,
            'can_manage_account' => $isAdmin,
            'can_manage_profile' => $isAdmin || $teacherCanManageStudents,
            'can_manage_documents' => $isAdmin || $teacherCanManageStudents,
            'can_manage_payments' => $isAdmin || ($teacherProfile?->can_manage_payments ?? false),
        ];

        return view('admin.clients.index', [
            'clients' => $clients,
            'phonePrefixes' => $phonePrefixes,
            'documentDefinitions' => $documentDefinitions,
            'membershipSummary' => $membershipSummary,
            'clientCount' => $clients->count(),
            'courseUnpaidSummary' => $this->loadCourseUnpaidSummary($showFutureCourses),
            'courseUnpaidShowFuture' => $showFutureCourses,
            'initialExpandedClient' => $request->integer('client_id') ?: null,
            'clientPagePermissions' => $clientPagePermissions,
        ]);
    }

    private function getTeacherStudentIds(int $teacherId): array
    {
        return Subscription::query()
            ->whereHas('course', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->pluck('client_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function loadClients(bool $autoGenerate, ?array $restrictToIds = null)
    {
        $manager = app(MembershipManager::class);
        $season = $manager->determineCurrentSeason();

        $currentYear = now()->year;

        if ($restrictToIds !== null && empty($restrictToIds)) {
            return collect();
        }

        $query = User::select([
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
            ->where('role', 'Client');

        if ($restrictToIds !== null) {
            $query->whereIn('id', $restrictToIds);
        }

        return $query
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
            'plan_type' => $meta['plan_type'] ?? null,
            'plan_label' => $planLabel,
            'routes' => [
                'update' => route('admin.payments.update', $payment->id),
                'receipt' => $payment->receipt_url ? route('admin.payments.receipt', $payment->id) : null,
                'reprint' => $payment->receipt_url ? route('admin.payments.reprint', $payment->id) : null,
            ],
            'receipt_available' => (bool) $payment->receipt_url,
            'receipt_route' => $payment->receipt_url ? route('admin.payments.receipt', $payment->id) : null,
            'is_pending' => $payment->status === 'pending',
        ];
    }

    private function shouldAutoGenerateMemberships(): bool
    {
        $setting = Setting::query()->find('membership_auto_generate');

        return isset($setting) ? (bool) $setting->value : false;
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

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
    }
}
