<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1F2937; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .company-info { font-size: 10px; color: #6B7280; }
        .company-name { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 22px; font-weight: 700; color: #10B981; margin-bottom: 4px; }
        .doc-title .ref { font-size: 12px; color: #6B7280; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 20px; padding: 10px 14px; background: #F9FAFB; border: 1px solid #E5E7EB; }
        .meta-block label { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #9CA3AF; letter-spacing: 0.05em; }
        .meta-block .val { font-size: 11px; font-weight: 600; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #10B981; color: #FFF; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #E5E7EB; font-size: 11px; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        .totals { width: 260px; margin-left: auto; }
        .totals tr td { padding: 6px 10px; }
        .totals .grand td { font-weight: 700; font-size: 13px; border-top: 2px solid #10B981; color: #10B981; }
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
                <div style="font-size:22px;font-weight:700;color:#10B981;">FACTURE</div>
                <div style="font-size:12px;color:#6B7280;">{{ $sale->reference }}</div>
                <div style="font-size:10px;color:#9CA3AF;margin-top:4px;">Date : {{ $sale->created_at->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    @if($sale->customer)
    <div style="margin-bottom:20px;padding:10px 14px;background:#F9FAFB;border:1px solid #E5E7EB;">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#9CA3AF;">Client</div>
        <div style="font-weight:600;">{{ $sale->customer->name }}</div>
        @if($sale->customer->phone)<div style="font-size:10px;color:#6B7280;">{{ $sale->customer->phone }}</div>@endif
        @if($sale->customer->address)<div style="font-size:10px;color:#6B7280;">{{ $sale->customer->address }}</div>@endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:45%;">Produit</th>
                <th class="text-right">Qté</th>
                <th class="text-right">P.U. (F)</th>
                <th class="text-right">Remise</th>
                <th class="text-right">Total (F)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                <td class="text-right">{{ $item->discount ? number_format($item->discount, 0, ',', ' ') : '—' }}</td>
                <td class="text-right">{{ number_format($item->total, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Sous-total</td><td class="text-right">{{ number_format($sale->subtotal, 0, ',', ' ') }} F</td></tr>
        @if($sale->discount_amount)<tr><td>Remise</td><td class="text-right">-{{ number_format($sale->discount_amount, 0, ',', ' ') }} F</td></tr>@endif
        @if($sale->tax_amount)<tr><td>TVA</td><td class="text-right">{{ number_format($sale->tax_amount, 0, ',', ' ') }} F</td></tr>@endif
        <tr class="grand"><td>TOTAL</td><td class="text-right">{{ number_format($sale->total, 0, ',', ' ') }} F CFA</td></tr>
    </table>

    @if($sale->notes)
    <div style="margin-top:10px;font-size:10px;color:#6B7280;"><strong>Notes :</strong> {{ $sale->notes }}</div>
    @endif

    <div class="footer">
        {{ $company->name }} — Facture générée automatiquement par Khayma
    </div>
</body>
</html>
