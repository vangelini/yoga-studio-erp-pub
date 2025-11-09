<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminCourseController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $this->validateCourse($request);

        DB::transaction(function () use ($data) {
            $course = Course::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'teacher_id' => $data['teacher_id'],
                'price' => $data['price'],
                'monthly_price' => $data['monthly_price'],
                'quarterly_price' => $data['quarterly_price'],
                'annual_price' => $data['annual_price'],
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
        $this->authorizeAdmin();

        $data = $this->validateCourse($request);

        DB::transaction(function () use ($course, $data) {
            $course->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'teacher_id' => $data['teacher_id'],
                'price' => $data['price'],
                'monthly_price' => $data['monthly_price'],
                'quarterly_price' => $data['quarterly_price'],
                'annual_price' => $data['annual_price'],
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

    protected function validateCourse(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'monthly_price' => ['nullable', 'numeric', 'min:0'],
            'quarterly_price' => ['nullable', 'numeric', 'min:0'],
            'annual_price' => ['nullable', 'numeric', 'min:0'],
            'speciality_description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'schedule_day' => ['nullable', 'array'],
            'schedule_day.*' => ['nullable', 'string'],
            'schedule_time' => ['nullable', 'array'],
            'schedule_time.*' => ['nullable', 'string'],
            'allows_extra_day' => ['nullable', 'boolean'],
            'extra_day_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $teacher = User::where('id', $data['teacher_id'])->where('role', 'Teacher')->exists();
        if (!$teacher) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Il docente selezionato non è valido.',
            ]);
        }

        $days = $request->input('schedule_day', []);
        $times = $request->input('schedule_time', []);

        $schedule = [];
        foreach ($days as $index => $day) {
            $day = trim((string) $day);
            $time = $times[$index] ?? null;
            if ($day && $time) {
                $schedule[] = [
                    'day' => $day,
                    'time' => $time,
                ];
            }
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
            'teacher_id' => (int) $data['teacher_id'],
            'price' => $monthlyPrice ?? 0,
            'monthly_price' => $monthlyPrice,
            'quarterly_price' => $quarterlyPrice,
            'annual_price' => $annualPrice,
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
            ]);
        }
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
    }
}
