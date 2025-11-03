<?php

use App\Http\Controllers\Web\AdminCourseController;
use App\Http\Controllers\Web\AdminUserController;
use App\Http\Controllers\Web\AuthSessionController;
use App\Http\Controllers\Web\DashboardPageController;
use App\Http\Controllers\Web\ClientBookingController;
use App\Http\Controllers\Web\ClientSubscriptionController;
use App\Http\Controllers\Web\PaymentController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
*/

Route::get('/', [AuthSessionController::class, 'showLoginForm'])->name('login');
Route::get('/register', [AuthSessionController::class, 'showRegisterForm'])->name('register');
Route::post('/login', [AuthSessionController::class, 'login'])->name('login.attempt');
Route::post('/register', [AuthSessionController::class, 'register'])->name('register.attempt');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthSessionController::class, 'logout'])->name('logout');

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        if ($request->user()->role === 'Client' && $request->user()->status === 'pending') {
            $request->user()->update(['status' => 'active']);
        }

        return redirect()->route('dashboard')->with('status', 'Email verificata con successo. Benvenuto!');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'La tua email è già verificata.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Ti abbiamo inviato un nuovo link di verifica.');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardPageController::class)->name('dashboard');

    Route::middleware('throttle:15,1')->group(function () {
        Route::get('/admin/users/export', [AdminUserController::class, 'export'])->name('admin.users.export');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'updateRoleStatus'])->name('admin.users.update');
        Route::put('/admin/users/{user}/profile', [AdminUserController::class, 'updateProfile'])->name('admin.users.profile');
        Route::post('/admin/users/{user}/password-email', [AdminUserController::class, 'sendPasswordReset'])->name('admin.users.passwordEmail');
        Route::post('/admin/users/{user}/resend-verification', [AdminUserController::class, 'resendVerification'])->name('admin.users.resendVerification');
        Route::post('/admin/users/{user}/activate', [AdminUserController::class, 'activate'])->name('admin.users.activate');
        Route::post('/admin/courses', [AdminCourseController::class, 'store'])->name('admin.courses.store');
        Route::put('/admin/courses/{course}', [AdminCourseController::class, 'update'])->name('admin.courses.update');
        Route::post('/admin/payments/{payment}', [\App\Http\Controllers\Web\PaymentAdminController::class, 'updateStatus'])->name('admin.payments.update');
        Route::get('/admin/payments/{payment}/receipt', [\App\Http\Controllers\Web\PaymentAdminController::class, 'showReceipt'])->name('admin.payments.receipt');
        Route::post('/admin/memberships/generate', [\App\Http\Controllers\Web\AdminMembershipController::class, 'generate'])->name('admin.memberships.generate');
        Route::get('/admin/settings', [\App\Http\Controllers\Web\AdminSettingController::class, 'edit'])->name('admin.settings.edit');
        Route::put('/admin/settings', [\App\Http\Controllers\Web\AdminSettingController::class, 'update'])->name('admin.settings.update');
        Route::post('/admin/teachers/{teacher}/private', [AdminUserController::class, 'togglePrivateClasses'])->name('admin.teachers.private');
        Route::post('/admin/teachers/{teacher}/courses', [AdminUserController::class, 'updateTeacherCourses'])->name('admin.teachers.courses');
    });

    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/client/bookings', [ClientBookingController::class, 'store'])->name('client.bookings.store');
        Route::delete('/client/bookings/{booking}', [ClientBookingController::class, 'destroy'])->name('client.bookings.destroy');
        Route::post('/client/subscriptions', [ClientSubscriptionController::class, 'store'])->name('client.subscriptions.store');
        Route::put('/client/subscriptions/{subscription}/toggle-renew', [ClientSubscriptionController::class, 'toggleRenewal'])->name('client.subscriptions.toggle');
        Route::delete('/client/subscriptions/{subscription}', [ClientSubscriptionController::class, 'destroy'])->name('client.subscriptions.destroy');
        Route::post('/client/payments/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('client.payments.markPaid');
    });
});
