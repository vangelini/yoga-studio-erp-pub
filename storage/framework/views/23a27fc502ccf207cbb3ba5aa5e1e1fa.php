<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Conferma email | Shanti Sadhana</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Helvetica Neue',Arial,sans-serif;color:#1f2937;">
<table width="100%" cellspacing="0" cellpadding="0" style="padding:32px 0;">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="0" style="background-color:#ffffff;border-radius:24px;padding:40px;border:1px solid #e5e7eb;">
                <tr>
                    <td align="center" style="padding-bottom:24px;">
                        <img src="<?php echo e(asset('images/yoga-logo-big.jpg')); ?>" alt="Shanti Sadhana" width="96" height="96" style="border-radius:50%;border:2px solid #14b8a6;">
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;padding-bottom:16px;">
                        <h1 style="margin:0;font-size:24px;font-weight:700;color:#0f172a;">Conferma il tuo indirizzo email</h1>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:15px;line-height:1.6;padding-bottom:24px;">
                        <p style="margin:0 0 12px;">Ciao,</p>
                        <p style="margin:0 0 12px;">Per completare la registrazione a Shanti Sadhana dobbiamo verificare che questa email ti appartenga. Premi il pulsante qui sotto per confermare.</p>
                        <p style="margin:0;">Se non hai creato tu l’account, puoi ignorare questo messaggio.</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-bottom:32px;">
                        <a href="<?php echo e($url); ?>" style="display:inline-block;padding:14px 28px;background:linear-gradient(135deg,#0d9488,#14b8a6);color:#ffffff;text-decoration:none;border-radius:999px;font-weight:600;font-size:14px;">Conferma indirizzo email</a>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:13px;line-height:1.6;color:#6b7280;padding-bottom:8px;">
                        Se il pulsante non dovesse funzionare, copia e incolla il seguente link nel browser:
                    </td>
                </tr>
                <tr>
                    <td style="font-size:13px;line-height:1.6;padding-bottom:24px;">
                        <a href="<?php echo e($url); ?>" style="color:#0d9488;word-break:break-all;"><?php echo e($url); ?></a>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:12px;color:#94a3b8;text-align:center;">
                        &copy; <?php echo e(date('Y')); ?> Shanti Sadhana Yoga Center · Questo messaggio è stato generato automaticamente, non rispondere.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
<?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/mail/verify-email.blade.php ENDPATH**/ ?>