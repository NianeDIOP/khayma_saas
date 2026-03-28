<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue {{ $user->name }}</title>
</head>
<body style="margin:0;background:#f9fafb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;">
                <tr>
                    <td style="padding:22px 24px;border-bottom:1px solid #e5e7eb;">
                        <h1 style="margin:0;font-size:20px;color:#06b6d4;">Bienvenue sur Khayma</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 24px;">
                        <p style="margin:0 0 8px;font-size:14px;">Bonjour {{ $user->name }},</p>
                        <p style="margin:0 0 14px;font-size:14px;line-height:1.6;">
                            Votre espace entreprise <strong>{{ $company->name }}</strong> est prêt.
                        </p>
                        <p style="margin:0 0 14px;font-size:14px;line-height:1.6;">
                            Connectez-vous pour commencer: clients, ventes, stocks, rapports et plus encore.
                        </p>
                        <p style="margin:0;font-size:13px;color:#6b7280;">Merci de votre confiance.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
