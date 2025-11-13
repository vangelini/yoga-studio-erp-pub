<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CoursePaymentGenerator;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminCoursePaymentController extends Controller
{
    public function generate(Request $request, CoursePaymentGenerator $generator): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'Admin';
        $teacherId = null;

        if ($isAdmin) {
            // allowed
        } elseif ($user?->role === 'Teacher' && optional($user->teacherProfile)->can_manage_payments) {
            $teacherId = $user->id;
        } else {
            abort(403);
        }

        $leadDaysSetting = Setting::query()->find('course_payment_lead_days');
        $leadDays = $leadDaysSetting ? (int) $leadDaysSetting->value : 10;

        $result = $generator->generate($leadDays, $teacherId);

        if ($isAdmin) {
            $this->storeRunLog($result, true);
        }

        $message = sprintf(
            'Generazione pendenze corsi completata. Verificati %d abbonamenti, create %d nuove pendenze.',
            $result['checked'],
            $result['created']
        );

        $redirectRoute = $isAdmin ? 'admin.settings.edit' : 'admin.clients.index';

        return redirect()->route($redirectRoute)->with('status', $message);
    }

    protected function storeRunLog(array $result, bool $manual = false): void
    {
        $payload = array_merge($result, [
            'manual' => $manual,
            'timestamp' => $result['run_at'] ?? now()->toDateTimeString(),
        ]);

        Setting::updateOrCreate(['key' => 'course_payment_last_run'], [
            'value' => json_encode($payload),
        ]);
    }
}
