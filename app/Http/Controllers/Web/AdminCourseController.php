<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminCourseController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        try {
            $data = $this->validateCourse($request);
        } catch (ValidationException $e) {
            Log::warning('Course create validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);

            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('status', 'Impossibile salvare il corso. Controlla i campi evidenziati e riprova.');
        }

        DB::transaction(function () use ($data) {
            $course = Course::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'teacher_id' => $data['teacher_id'],
                'price' => $data['price'],
                'monthly_price' => $data['monthly_price'],
                'quarterly_price' => $data['quarterly_price'],
                'annual_price' => $data['annual_price'],
                'pricing_mode' => $data['pricing_mode'],
                'max_enrollments' => $data['max_enrollments'],
                'lesson_pricing' => $data['lesson_pricing'],
                'allows_extra_day' => $data['allows_extra_day'],
                'extra_day_discount_percent' => $data['extra_day_discount_percent'],
                'speciality_description' => $data['speciality_description'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);

            $this->syncSchedule($course, $data['schedule']);
        });

        return redirect()
            ->route('dashboard')
            ->with('status', 'Corso creato con successo.');
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourseUpdate($request->user(), $course);

        try {
            $data = $this->validateCourse($request);
        } catch (ValidationException $e) {
            Log::warning('Course update validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
                'course_id' => $course->id,
            ]);

            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('status', 'Impossibile salvare il corso. Controlla i campi evidenziati e riprova.');
        }

        if ($request->user()->role === 'Teacher') {
            $data['teacher_id'] = $course->teacher_id;
        }

        DB::transaction(function () use ($course, $data) {
            $course->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'teacher_id' => $data['teacher_id'],
                'price' => $data['price'],
                'monthly_price' => $data['monthly_price'],
                'quarterly_price' => $data['quarterly_price'],
                'annual_price' => $data['annual_price'],
                'pricing_mode' => $data['pricing_mode'],
                'max_enrollments' => $data['max_enrollments'],
                'lesson_pricing' => $data['lesson_pricing'],
                'allows_extra_day' => $data['allows_extra_day'],
                'extra_day_discount_percent' => $data['extra_day_discount_percent'],
                'speciality_description' => $data['speciality_description'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);

            $course->schedule()->delete();
            $this->syncSchedule($course, $data['schedule']);
        });

        return redirect()
            ->route('dashboard')
            ->with('status', 'Corso aggiornato con successo.');
    }

    private function authorizeCourseUpdate(?User $user, Course $course): void
    {
        abort_unless($user, 403);

        if ($user->role === 'Admin') {
            return;
        }

        if ($user->role === 'Teacher') {
            $teacher = $user->teacherProfile;
            abort_unless($teacher && $teacher->can_manage_courses, 403);
            abort_unless((int) $course->teacher_id === (int) $user->id, 403);
            return;
        }

        abort(403);
    }

    protected function validateCourse(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'monthly_price' => ['nullable', 'numeric', 'min:0'],
            'quarterly_price' => ['nullable', 'numeric', 'min:0'],
            'annual_price' => ['nullable', 'numeric', 'min:0'],
            'pricing_mode' => ['required', Rule::in(['block', 'per_lesson'])],
            'max_enrollments' => ['nullable', 'integer', 'min:1'],
            'lesson_pricing' => ['nullable', 'array'],
            'lesson_pricing.*' => ['nullable', 'array'],
            'lesson_pricing.*.*' => ['nullable', 'numeric', 'min:0'],
            'speciality_description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'schedule_day' => ['nullable', 'array'],
            'schedule_day.*' => ['nullable', 'string'],
            'schedule_time' => ['nullable', 'array'],
            'schedule_time.*' => ['nullable', 'string'],
            'schedule_capacity' => ['nullable', 'array'],
            'schedule_capacity.*' => ['nullable', 'integer', 'min:1'],
            'allows_extra_day' => ['nullable', 'boolean'],
            'extra_day_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if (!empty($data['teacher_id'])) {
            $teacher = User::where('id', $data['teacher_id'])->where('role', 'Teacher')->exists();
            if (!$teacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Insegnante selezionato non valido.',
                ]);
            }
        }

        $days = $request->input('schedule_day', []);
        $times = $request->input('schedule_time', []);

        $capacities = $request->input('schedule_capacity', []);

        $schedule = [];
        foreach ($days as $index => $day) {
            $day = trim((string) $day);
            $time = $times[$index] ?? null;
            if ($day && $time) {
                $schedule[] = [
                    'day' => $day,
                    'time' => $time,
                    'capacity' => isset($capacities[$index]) && $capacities[$index] !== ''
                        ? (int) $capacities[$index]
                        : null,
                ];
            }
        }

        $lessonPricingInput = $request->input('lesson_pricing', []);
        $lessonPricing = [];
        $maxLessons = max(count($schedule), 1);

        foreach ($lessonPricingInput as $plan => $entries) {
            foreach (($entries ?? []) as $lessons => $amount) {
                if ($amount === null || $amount === '') {
                    continue;
                }
                if ((int) $lessons > $maxLessons) {
                    continue;
                }
                $amount = (float) $amount;
                if ($amount > 0) {
                    $lessonPricing[$plan][$lessons] = $amount;
                }
            }
        }

        if ($data['pricing_mode'] !== 'per_lesson') {
            $lessonPricing = [];
        }

        $monthlyPrice = $data['monthly_price'] ?? $data['price'] ?? null;
        $quarterlyPrice = $data['quarterly_price'] ?? null;
        $annualPrice = $data['annual_price'] ?? null;

        $monthlyPrice = isset($monthlyPrice) ? (float) $monthlyPrice : null;
        $quarterlyPrice = isset($quarterlyPrice) ? (float) $quarterlyPrice : null;
        $annualPrice = isset($annualPrice) ? (float) $annualPrice : null;

        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'teacher_id' => isset($data['teacher_id']) ? (int) $data['teacher_id'] : null,
            'price' => $monthlyPrice ?? 0,
            'monthly_price' => $monthlyPrice,
            'quarterly_price' => $quarterlyPrice,
            'annual_price' => $annualPrice,
            'pricing_mode' => $data['pricing_mode'],
            'max_enrollments' => !empty($data['max_enrollments']) ? (int) $data['max_enrollments'] : null,
            'lesson_pricing' => $lessonPricing ?: null,
            'allows_extra_day' => (bool) ($request->boolean('allows_extra_day')),
            'extra_day_discount_percent' => isset($data['extra_day_discount_percent'])
                ? (float) $data['extra_day_discount_percent']
                : 0,
            'speciality_description' => $data['speciality_description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'schedule' => $schedule,
        ];
    }

    protected function syncSchedule(Course $course, array $schedule): void
    {
        foreach ($schedule as $slot) {
            CourseSchedule::create([
                'course_id' => $course->id,
                'day_of_week' => $slot['day'],
                'time' => $slot['time'],
                'capacity' => $slot['capacity'] ?? null,
            ]);
        }
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
    }
}
