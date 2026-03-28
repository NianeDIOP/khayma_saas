<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Stock</title>
</head>
<body style="margin:0;background:#f9fafb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;">
                <tr>
                    <td style="padding:22px 24px;border-bottom:1px solid #e5e7eb;">
                        <h1 style="margin:0;font-size:20px;color:#ef4444;">Alerte Stock Bas</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 24px;">
                        <p style="margin:0 0 8px;font-size:14px;">Bonjour {{ $recipientName }},</p>
                        <p style="margin:0 0 12px;font-size:14px;line-height:1.6;">
                            Les produits suivants de <strong>{{ $companyName }}</strong> sont sous le seuil minimum.
                        </p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #e5e7eb;">
                            <thead>
                                <tr style="background:#f3f4f6;">
                                    <th align="left" style="padding:8px;border-bottom:1px solid #e5e7eb;font-size:12px;">Produit</th>
                                    <th align="right" style="padding:8px;border-bottom:1px solid #e5e7eb;font-size:12px;">Qté</th>
                                    <th align="right" style="padding:8px;border-bottom:1px solid #e5e7eb;font-size:12px;">Seuil</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $p)
                                    <tr>
                                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;font-size:13px;">{{ $p['name'] }}</td>
                                        <td align="right" style="padding:8px;border-bottom:1px solid #f3f4f6;font-size:13px;">{{ $p['quantity'] }}</td>
                                        <td align="right" style="padding:8px;border-bottom:1px solid #f3f4f6;font-size:13px;">{{ $p['threshold'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p style="margin:12px 0 0;font-size:13px;color:#6b7280;">Pensez à réapprovisionner rapidement.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
