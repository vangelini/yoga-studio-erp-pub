<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title><?php echo e($template['document_title'] ?? 'Ricevuta'); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.5; margin: 0; padding: 24px; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { font-size: 20px; margin-bottom: 4px; }
        .section { margin-bottom: 18px; }
        .section-title { font-weight: bold; text-transform: uppercase; letter-spacing: .08em; font-size: 11px; color: #0f766e; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
        th { background: #f3f4f6; text-transform: uppercase; font-size: 10px; letter-spacing: .06em; }
        .totals { margin-top: 16px; text-align: right; }
        .totals strong { font-size: 14px; }
        .footer { margin-top: 28px; font-size: 11px; text-align: center; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e($template['document_title'] ?? 'Ricevuta'); ?></h1>
        <p><strong><?php echo e($template['association_name'] ?? ''); ?></strong></p>
        <p><?php echo e($template['association_address'] ?? ''); ?></p>
        <p><?php echo e($template['association_email'] ?? ''); ?> <?php echo e($template['association_phone'] ?? ''); ?></p>
    </div>

    <div class="section">
        <div class="section-title">Dettagli ricevuta</div>
        <p><strong>Numero:</strong> <?php echo e($receiptNumber); ?> / <?php echo e($receiptYear); ?></p>
        <p><strong>Data:</strong> <?php echo e($issuedAt); ?></p>
        <p><strong>Intestata a:</strong> <?php echo e($clientName); ?></p>
        <?php if($clientTaxCode): ?>
            <p><strong>Codice fiscale:</strong> <?php echo e($clientTaxCode); ?></p>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">Causale</div>
        <table>
            <thead>
                <tr>
                    <th>Descrizione</th>
                    <th>Periodo</th>
                    <th>Importo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo e($description); ?></td>
                    <td><?php echo e($period); ?></td>
                    <td><?php echo e($amount); ?></td>
                </tr>
            </tbody>
        </table>
        <?php if($notes): ?>
            <p style="margin-top: 12px;"><strong>Note:</strong> <?php echo e($notes); ?></p>
        <?php endif; ?>
    </div>

    <div class="totals">
        <p><strong>Totale corrisposto: <?php echo e($amount); ?></strong></p>
    </div>

    <div class="footer">
        <p><?php echo e($template['footer_note'] ?? ''); ?></p>
    </div>
</body>
</html>
<?php /**PATH /Users/vincenzo/Documents/shanti-sadhana-yoga-center/php-laravel/resources/views/receipts/pdf.blade.php ENDPATH**/ ?>