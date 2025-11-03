<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

class CashReceiptService
{
    public function generate(Payment $payment, ?string $notes = null): string
    {
        $year = now()->year;
        $nextNumber = $this->nextReceiptNumber($year);
        $template = $this->loadTemplate();

        $amountFormatted = sprintf('%s %s', config('receipt.currency_symbol', '€'), number_format($payment->amount, 2, ',', '.'));
        $period = optional($payment->due_date)->translatedFormat('F Y') ?: '—';

        $html = view('receipts.pdf', [
            'template' => $template,
            'receiptNumber' => str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT),
            'receiptYear' => $year,
            'issuedAt' => now()->format('d/m/Y H:i'),
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
        return match ($payment->type) {
            'membership' => 'Quota associativa annuale',
            'course_subscription' => $payment->meta['course_title'] ?? 'Lezione/Corso mensile',
            'private_lesson' => 'Lezione privata',
            default => 'Pagamento',
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
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => $tempDir,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);

            $mpdf->PDFA = true;
            $mpdf->PDFAauto = true;
            $mpdf->setAutoTopMargin('pad');
            $mpdf->setAutoBottomMargin('pad');

            if ($ownerPassword || $userPassword) {
                $owner = $ownerPassword ?: ($userPassword ?: 'ShantiSadhanaOwner');
                $mpdf->SetProtection(['print', 'copy'], $userPassword ?: null, $owner, 128);
            }

            $mpdf->WriteHTML($html);

            return $mpdf->Output(null, 'S');
        } catch (MpdfException $exception) {
            throw new RuntimeException('Impossibile generare la ricevuta PDF.', 0, $exception);
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
}
