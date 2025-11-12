<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Teacher;
use Illuminate\View\View;

class AdminTeacherPageController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        $teachers = Teacher::with(['user.documents', 'courses:id,title,teacher_id'])
            ->get()
            ->sortBy(fn (Teacher $teacher) => $teacher->user->name)
            ->values();

        return view('admin.teachers.index', [
            'teacherAdminList' => $teachers,
            'teacherCount' => $teachers->count(),
            'private_lessons_enabled' => (bool) optional(Setting::find('private_lessons_enabled'))->value,
        ]);
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
    }
}
