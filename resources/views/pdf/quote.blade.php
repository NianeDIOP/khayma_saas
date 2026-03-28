<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1F2937; line-height: 1.5; }
        .company-name { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .company-info { font-size: 10px; color: #6B7280; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #6366F1; color: #FFF; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #E5E7EB; font-size: 11px; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        .totals { width: 260px; margin-left: auto; }
        .totals tr td { padding: 6px 10px; }
        .totals .grand td { font-weight: 700; font-size: 13px; border-top: 2px solid #6366F1; color: #6366F1; }
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #E5E7EB; font-size: 9px; color: #9CA3AF; text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <table style="width:100%;margin-bottom:30px;border:none;">
        <tr>
            <td style="border:none;padding:0;width:60%;">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-info">
                    @if($company->address){{ $company->address }}<br>@endif
                    @if($company->phone)Tél : {{ $company->phone }}<br>@endif
                    @if($company->email){{ $company->email }}<br>@endif
                    @if($company->ninea)NINEA : {{ $company->ninea }}@endif
                </div>
            </td>
            <td style="border:none;padding:0;text-align:right;">
                <div style="font-size:22px;font-weight:700;color:#6366F1;">DEVIS</div>
                <div style="font-size:12px;color:#6B7280;">{{ $quote->reference }}</div>
                <div style="font-size:10px;color:#9CA3AF;margin-top:4px;">Date : {{ $quote->created_at->format('d/m/Y') }}</div>
                @if($quote->valid_until)
                <div style="font-size:10px;color:#D97706;margin-top:2px;">Valide jusqu'au : {{ \Carbon\Carbon::parse($quote->valid_until)->format('d/m/Y') }}</div>
                @endif
            </td>
        </tr>
    </table>

    @if($quote->customer)
    <div style="margin-bottom:20px;padding:10px 14px;background:#F9FAFB;border:1px solid #E5E7EB;">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#9CA3AF;">Client</div>
        <div style="font-weight:600;">{{ $quote->customer->name }}</div>
        @if($quote->customer->phone)<div style="font-size:10px;color:#6B7280;">{{ $quote->customer->phone }}</div>@endif
        @if($quote->customer->address)<div style="font-size:10px;color:#6B7280;">{{ $quote->customer->address }}</div>@endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:45%;">Produit</th>
                <th class="text-right">Qté</th>
                <th class="text-right">P.U. (F)</th>
                <th class="text-right">Total (F)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($item->total, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        @if($quote->discount_amount)<tr><td>Remise</td><td class="text-right">-{{ number_format($quote->discount_amount, 0, ',', ' ') }} F</td></tr>@endif
        @if($quote->tax_amount)<tr><td>TVA</td><td class="text-right">{{ number_format($quote->tax_amount, 0, ',', ' ') }} F</td></tr>@endif
        <tr class="grand"><td>TOTAL</td><td class="text-right">{{ number_format($quote->total, 0, ',', ' ') }} F CFA</td></tr>
    </table>

    @if($quote->notes)
    <div style="margin-top:10px;font-size:10px;color:#6B7280;"><strong>Notes :</strong> {{ $quote->notes }}</div>
    @endif

    <div style="margin-top:30px;padding:14px;background:#EEF2FF;border:1px solid #C7D2FE;">
        <div style="font-size:10px;font-weight:700;color:#4F46E5;margin-bottom:4px;">CONDITIONS</div>
        <div style="font-size:10px;color:#6B7280;">Ce devis est valable pour une durée de 30 jours. Passé ce délai, les prix et conditions peuvent être modifiés. L'acceptation de ce devis vaut bon de commande.</div>
    </div>

    <div class="footer">
        {{ $company->name }} — Devis généré automatiquement par Khayma
    </div>
</body>
</html>
