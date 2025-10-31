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
                'speciality_description' => $data['speciality_description'],
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
                'speciality_description' => $data['speciality_description'],
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
            'price' => ['required', 'numeric', 'min:0'],
            'speciality_description' => ['nullable', 'string'],
            'schedule_day' => ['nullable', 'array'],
            'schedule_day.*' => ['nullable', 'string'],
            'schedule_time' => ['nullable', 'array'],
            'schedule_time.*' => ['nullable', 'string'],
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

        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'teacher_id' => (int) $data['teacher_id'],
            'price' => $data['price'],
            'speciality_description' => $data['speciality_description'] ?? null,
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
