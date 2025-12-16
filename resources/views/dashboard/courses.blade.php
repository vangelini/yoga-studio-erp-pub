@extends('layouts.app')

@php
    $viewConfig = $dashboardViewConfig ?? [];
    $showCourseAdmin = $viewConfig['show_course_admin'] ?? true;
    $allowCourseCreation = $viewConfig['allow_course_creation'] ?? true;
    $allowTeacherSelection = $viewConfig['allow_teacher_selection'] ?? true;
    $allowStudentManage = $viewConfig['allow_student_manage'] ?? true;
    $courseCardTitle = $viewConfig['course_card_title'] ?? 'Gestione corsi';
    $courseCardSubtitle = $viewConfig['course_card_subtitle'] ?? 'I campi contrassegnati con <span class="text-rose-600 font-semibold">*</span> sono obbligatori.';
    $courseCardTeacherId = $viewConfig['current_teacher_id'] ?? null;
    $teacherAdminList = $extra['teacherAdminList'] ?? ($teacherAdminList ?? collect());
    $teacherSelectOptions = $teacherAdminList
        ->mapWithKeys(fn ($teacher) => [$teacher->user_id => $teacher->user->name])
        ->sort();
    $dayOptions = ["Lunedi", "Martedi", "Mercoledi", "Giovedi", "Venerdi", "Sabato", "Domenica"];
@endphp

@section('content')
    <section class="space-y-8">
        @if($showCourseAdmin)
            @include('dashboard.partials.admin-courses', [
                'teacherOptions' => $teacherSelectOptions,
                'dayOptions' => $dayOptions,
                'allowCourseCreation' => $allowCourseCreation,
                'allowTeacherSelection' => $allowTeacherSelection,
                'courseCardTitle' => $courseCardTitle,
                'courseCardSubtitle' => $courseCardSubtitle,
                'currentTeacherId' => $courseCardTeacherId,
                'allowStudentManage' => $allowStudentManage,
                'viewMode' => $viewConfig['mode'] ?? 'admin',
            ])
        @else
            <div class="card p-6">
                <p class="text-sm text-stone-600">Non hai permessi per gestire i corsi.</p>
            </div>
        @endif
    </section>
@endsection
