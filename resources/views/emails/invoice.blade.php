<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $sale->reference }}</title>
</head>
<body style="margin:0;background:#f9fafb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;">
                <tr>
                    <td style="padding:22px 24px;border-bottom:1px solid #e5e7eb;">
                        <h1 style="margin:0;font-size:20px;color:#10b981;">Khayma</h1>
                        <p style="margin:8px 0 0;font-size:13px;color:#6b7280;">Votre facture est disponible.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 24px;">
                        <p style="margin:0 0 8px;font-size:14px;">Bonjour {{ $sale->customer?->name ?? 'Client' }},</p>
                        <p style="margin:0 0 14px;font-size:14px;line-height:1.6;">
                            Merci pour votre achat chez <strong>{{ $sale->company->name }}</strong>.
                        </p>
                        <div style="background:#f3f4f6;border:1px solid #e5e7eb;padding:12px 14px;margin-bottom:14px;">
                            <div style="font-size:13px;margin-bottom:6px;"><strong>Référence :</strong> {{ $sale->reference }}</div>
                            <div style="font-size:13px;margin-bottom:6px;"><strong>Date :</strong> {{ $sale->created_at?->format('d/m/Y H:i') }}</div>
                            <div style="font-size:13px;"><strong>Montant total :</strong> {{ number_format((float) $sale->total, 0, ',', ' ') }} XOF</div>
                        </div>
                        <p style="margin:0;font-size:13px;color:#6b7280;">Ce message a été envoyé automatiquement par Khayma.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
