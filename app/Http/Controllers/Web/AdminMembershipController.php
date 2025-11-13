<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Services\MembershipManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminMembershipController extends Controller
{
    public function generate(Request $request, MembershipManager $manager): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'Admin';
        $teacherId = null;

        if ($isAdmin) {
            // ok
        } elseif ($user?->role === 'Teacher' && optional($user->teacherProfile)->can_manage_payments) {
            $teacherId = $user->id;
        } else {
            abort(403);
        }

        $clientsQuery = User::where('role', 'Client');

        if ($teacherId) {
            $studentIds = $this->getTeacherStudentIds($teacherId);
            if (empty($studentIds)) {
                return redirect()->route('admin.clients.index')->with('status', 'Nessun allievo associato ai tuoi corsi richiede la generazione delle quote.');
            }
            $clientsQuery->whereIn('id', $studentIds);
        }

        $clients = $clientsQuery->get();
        $created = 0;

        foreach ($clients as $client) {
            $membership = $manager->ensureCurrentMembership($client, false);
            $payment = $membership->payment;

            if (!$payment) {
                $membership = $manager->ensureCurrentMembership($client, true);
                $payment = $membership->payment;
                if ($payment && $payment->status === 'pending') {
                    $created++;
                }
                continue;
            }

            if ($payment->status === 'pending') {
                $manager->ensureCurrentMembership($client, true);
            }
        }

        $redirectRoute = $isAdmin ? 'admin.settings.edit' : 'admin.clients.index';

        return redirect()
            ->route($redirectRoute)
            ->with('status', "Generazione quote completata. Nuove pendenze create: {$created}");
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
}
