<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\MembershipSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

class CashReceiptService
{
    public function generate(Payment $payment, ?string $notes = null, bool $reuseExisting = false): string
    {
        $now = now();
        $shouldReuse = $reuseExisting && $payment->receipt_number && $payment->receipt_year;

        $year = $shouldReuse ? $payment->receipt_year : $now->year;
        $nextNumber = $shouldReuse ? $payment->receipt_number : $this->nextReceiptNumber($year);
        $template = $this->loadTemplate();

        $amountFormatted = sprintf('%s %s', config('receipt.currency_symbol', '€'), number_format($payment->amount, 2, ',', '.'));
        $period = $this->referencePeriod($payment);

        $html = view('receipts.pdf', [
            'template' => $template,
            'receiptNumber' => str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT),
            'receiptYear' => $year,
            'issuedAt' => $now->format('d/m/Y H:i'),
            'clientName' => optional($payment->user)->name,
            'clientTaxCode' => optional($payment->user)->codice_fiscale,
            'description' => $this->descriptionForPayment($payment),
            'period' => Str::ucfirst($period),
            'amount' => $amountFormatted,
            'notes' => $notes,
        ])->render();

        $userPassword = $this->resolveUserPassword($payment);
        $ownerPassword = $this->getSetting('receipt_owner_password', config('receipt.owner_password'));

        $pdfBinary = $this->renderPdf($html, $ownerPassword, $userPassword);

        $disk = config('receipt.storage_disk', 'public');
        $directory = trim(config('receipt.storage_directory', 'receipts'), '/');
        $filename = sprintf('receipt-%d-%04d.pdf', $year, $nextNumber);
        $path = $directory . '/' . $year;
        Storage::disk($disk)->makeDirectory($path);
        $fullPath = $path . '/' . $filename;
        Storage::disk($disk)->put($fullPath, $pdfBinary);

        $payment->forceFill([
            'receipt_number' => $nextNumber,
            'receipt_year' => $year,
            'receipt_path' => $fullPath,
        ])->save();

        return Storage::disk($disk)->path($fullPath);
    }

    public function reprint(Payment $payment, ?string $notes = null): string
    {
        $disk = config('receipt.storage_disk', 'public');

        if ($payment->receipt_path && Storage::disk($disk)->exists($payment->receipt_path)) {
            $this->archiveExistingReceipt($payment);
        }

        $shouldReuse = (bool) ($payment->receipt_number && $payment->receipt_year);

        return $this->generate($payment, $notes, $shouldReuse);
    }

    protected function nextReceiptNumber(int $year): int
    {
        $max = Payment::where('receipt_year', $year)->max('receipt_number');
        return $max ? $max + 1 : 1;
    }

    protected function loadTemplate(): array
    {
        $path = config('receipt.template_path');

        if (!$path || !file_exists($path)) {
            throw new RuntimeException('Receipt template JSON non trovato.');
        }

        $contents = file_get_contents($path);
        $data = json_decode($contents, true);

        if (!is_array($data)) {
            throw new RuntimeException('Receipt template JSON non valido.');
        }

        return $data;
    }

    protected function descriptionForPayment(Payment $payment): string
    {
        $planLabel = $this->planTypeLabel($payment->meta['plan_type'] ?? null);

        return match ($payment->type) {
            'membership' => trim('Quota associativa ' . ($planLabel ? '(' . $planLabel . ')' : '')),
            'course_subscription' => trim(($payment->meta['course_title'] ?? 'Lezione/Corso') . ($planLabel ? ' - Abbonamento: ' . $planLabel . '' : '')),
            'private_lesson' => 'Lezione privata',
            default => 'Pagamento' . ($planLabel ? ' (' . $planLabel . ')' : ''),
        };
    }

    /**
     * Render the receipt HTML into a PDF/A-compliant protected document.
     */
    protected function renderPdf(string $html, ?string $ownerPassword = null, ?string $userPassword = null): string
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        try {
            $isProtected = (bool) ($ownerPassword || $userPassword);

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => $tempDir,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);

            if (!$isProtected) {
                $mpdf->PDFA = true;
                $mpdf->PDFAauto = true;
            }

            if ($isProtected) {
                $owner = $ownerPassword ?: ($userPassword ?: 'ShantiSadhanaOwner');
                $mpdf->SetProtection(['print', 'copy'], $userPassword ?: null, $owner, 128);
            }

            $mpdf->WriteHTML($html);

            return $mpdf->Output(null, 'S');
        } catch (MpdfException $exception) {
            $message = $exception->getMessage();
            throw new RuntimeException(
                'Impossibile generare la ricevuta PDF. ' . ($message ? 'Dettagli: ' . $message : ''),
                0,
                $exception
            );
        }
    }

    protected function resolveUserPassword(Payment $payment): ?string
    {
        $mode = $this->getSetting('receipt_user_password_mode', 'blank');
        $customSetting = $this->getSetting('receipt_user_password_custom');
        $defaultCustom = config('receipt.user_password');

        return match ($mode) {
            'email' => optional($payment->user)->email ?: null,
            'custom' => $customSetting ?: $defaultCustom ?: null,
            default => null,
        };
    }

    protected function getSetting(string $key, $default = null)
    {
        $setting = Setting::query()->find($key);
        return $setting?->value ?? $default;
    }

    private function planTypeLabel(?string $planType): ?string
    {
        return match ($planType) {
            'monthly' => 'Mensile',
            'quarterly' => 'Trimestrale',
            'annual' => 'Annuale',
            default => $planType ? ucfirst($planType) : null,
        };
    }

    private function referencePeriod(Payment $payment): string
    {
        $meta = $payment->meta ?? [];
        $planType = $meta['plan_type'] ?? null;
        $period = $meta['period_label'] ?? null;

        // Prefer subscription dates for trimestrali.
        if (!$period && $planType === 'quarterly' && $payment->payable instanceof Subscription) {
            $start = $payment->payable->start_date;
            $end = $payment->payable->end_date;
            if ($start && $end) {
                return $start->translatedFormat('F Y') . ' - ' . $end->translatedFormat('F Y');
            }
        }

        // Membership annuale: se disponibile uso il range della membership.
        if (!$period && $planType === 'annual' && $payment->payable instanceof MembershipSubscription) {
            $start = $payment->payable->starts_at;
            $end = $payment->payable->ends_at;
            if ($start && $end) {
                // stesso anno -> solo anno, altrimenti range
                return $start->format('Y') === $end->format('Y')
                    ? $start->format('Y')
                    : $start->translatedFormat('d/m/Y') . ' - ' . $end->translatedFormat('d/m/Y');
            }
        }

        // Fallback basato sulla due_date.
        $due = $payment->due_date ? Carbon::parse($payment->due_date) : null;
        if ($due) {
            return match ($planType) {
                'monthly' => $due->translatedFormat('F Y'),
                'quarterly' => $due->copy()->subMonths(2)->translatedFormat('F') . ' - ' . $due->translatedFormat('F Y'),
                'annual' => $due->format('Y'),
                default => $due->translatedFormat('F Y'),
            };
        }

        return '—';
    }

    protected function archiveExistingReceipt(Payment $payment): ?string
    {
        $disk = config('receipt.storage_disk', 'public');

        if (!$payment->receipt_path || !Storage::disk($disk)->exists($payment->receipt_path)) {
            return null;
        }

        $timestamp = now()->format('YmdHis');
        $pathInfo = pathinfo($payment->receipt_path);
        $directory = $pathInfo['dirname'] ?? '';
        $directory = $directory === '.' ? '' : $directory . '/';
        $filename = $pathInfo['filename'] ?? 'receipt';
        $extension = $pathInfo['extension'] ?? 'pdf';

        $archivedName = sprintf('%s_annullata_%s.%s', $filename, $timestamp, $extension);
        $archivedPath = $directory . $archivedName;

        Storage::disk($disk)->move($payment->receipt_path, $archivedPath);

        return $archivedPath;
    }
}
