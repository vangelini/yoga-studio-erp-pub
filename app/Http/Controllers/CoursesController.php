<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validateCourse($request);

        return DB::transaction(function () use ($data) {
            $course = Course::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'teacher_id' => $data['teacherId'],
                'price' => $data['price'],
                'speciality_description' => $data['specialityDescription'] ?? null,
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'gallery' => $data['gallery'] ?? [],
            ]);

            $schedulePayload = $data['schedule'] ?? [];
            foreach ($schedulePayload as $slot) {
                CourseSchedule::create([
                    'course_id' => $course->id,
                    'day_of_week' => $slot['day'],
                    'time' => $slot['time'],
                ]);
            }

            $course->load('schedule');

            return response()->json([
                'course' => $this->formatCourse($course),
            ], 201);
        });
    }

    public function update(Request $request, Course $course)
    {
        $data = $this->validateCourse($request);

        return DB::transaction(function () use ($course, $data) {
            $course->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'teacher_id' => $data['teacherId'],
                'price' => $data['price'],
                'speciality_description' => $data['specialityDescription'] ?? null,
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'gallery' => $data['gallery'] ?? [],
            ]);

            $course->schedule()->delete();

            $schedulePayload = $data['schedule'] ?? [];
            foreach ($schedulePayload as $slot) {
                CourseSchedule::create([
                    'course_id' => $course->id,
                    'day_of_week' => $slot['day'],
                    'time' => $slot['time'],
                ]);
            }

            $course->load('schedule');

            return response()->json([
                'course' => $this->formatCourse($course),
            ]);
        });
    }

    private function validateCourse(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'teacherId' => ['required', 'integer', 'exists:users,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'specialityDescription' => ['nullable', 'string'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string'],
            'schedule' => ['nullable', 'array'],
            'schedule.*.day' => ['required_with:schedule', 'string'],
            'schedule.*.time' => ['required_with:schedule', 'string'],
        ]);
    }

    private function formatCourse(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'teacherId' => $course->teacher_id,
            'teacher_id' => $course->teacher_id,
            'price' => $course->price,
            'specialityDescription' => $course->speciality_description,
            'speciality_description' => $course->speciality_description,
            'start_date' => optional($course->start_date)?->format('Y-m-d'),
            'end_date' => optional($course->end_date)?->format('Y-m-d'),
            'startDate' => optional($course->start_date)?->format('Y-m-d'),
            'endDate' => optional($course->end_date)?->format('Y-m-d'),
            'gallery' => $course->gallery ?? [],
            'schedule' => $course->schedule->map(function ($slot) {
                return [
                    'day' => $slot->day_of_week,
                    'time' => $slot->time ? $slot->time->format('H:i') : null,
                ];
            })->all(),
            'startDateDisplay' => optional($course->start_date)?->format('d/m/Y'),
            'endDateDisplay' => optional($course->end_date)?->format('d/m/Y'),
        ];
    }
}
