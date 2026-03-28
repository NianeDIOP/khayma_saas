<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1F2937; line-height: 1.5; }
        .company-name { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .company-info { font-size: 10px; color: #6B7280; }
        .section-title { font-size: 12px; font-weight: 700; color: #10B981; margin: 18px 0 8px; text-transform: uppercase; border-bottom: 2px solid #10B981; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .detail-table td { padding: 5px 10px; font-size: 11px; border: none; }
        .detail-table td:first-child { font-weight: 600; color: #6B7280; width: 35%; }
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #E5E7EB; font-size: 9px; color: #9CA3AF; text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <!-- Header -->
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
                <div style="font-size:22px;font-weight:700;color:#10B981;">CONTRAT DE LOCATION</div>
                <div style="font-size:12px;color:#6B7280;">{{ $contract->reference }}</div>
                <div style="font-size:10px;color:#9CA3AF;margin-top:4px;">Créé le : {{ $contract->created_at->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Client -->
    @if($contract->customer)
    <div style="margin-bottom:16px;padding:10px 14px;background:#F9FAFB;border:1px solid #E5E7EB;">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#9CA3AF;">Locataire</div>
        <div style="font-weight:600;">{{ $contract->customer->name }}</div>
        @if($contract->customer->phone)<div style="font-size:10px;color:#6B7280;">{{ $contract->customer->phone }}</div>@endif
        @if($contract->customer->address)<div style="font-size:10px;color:#6B7280;">{{ $contract->customer->address }}</div>@endif
    </div>
    @endif

    <!-- Bien loué -->
    <div class="section-title">Bien loué</div>
    <table class="detail-table">
        @if($contract->rentalAsset)
        <tr><td>Désignation</td><td>{{ $contract->rentalAsset->name }}</td></tr>
        @if($contract->rentalAsset->description)<tr><td>Description</td><td>{{ $contract->rentalAsset->description }}</td></tr>@endif
        @if($contract->rentalAsset->address)<tr><td>Adresse</td><td>{{ $contract->rentalAsset->address }}</td></tr>@endif
        @endif
    </table>

    <!-- Conditions du contrat -->
    <div class="section-title">Conditions du contrat</div>
    <table class="detail-table">
        <tr><td>Date de début</td><td>{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</td></tr>
        @if($contract->end_date)<tr><td>Date de fin</td><td>{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</td></tr>@endif
        <tr><td>Loyer mensuel</td><td style="font-weight:700;color:#10B981;">{{ number_format($contract->monthly_rent, 0, ',', ' ') }} F CFA</td></tr>
        @if($contract->deposit)<tr><td>Caution</td><td>{{ number_format($contract->deposit, 0, ',', ' ') }} F CFA</td></tr>@endif
        <tr>
            <td>Statut</td>
            <td>
                @if($contract->status === 'active')<span style="color:#10B981;font-weight:600;">Actif</span>
                @elseif($contract->status === 'expired')<span style="color:#EF4444;font-weight:600;">Expiré</span>
                @elseif($contract->status === 'terminated')<span style="color:#6B7280;font-weight:600;">Résilié</span>
                @else<span>{{ ucfirst($contract->status) }}</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- Paiements -->
    @if($contract->payments && $contract->payments->count())
    <div class="section-title">Historique des paiements</div>
    <table>
        <thead>
            <tr>
                <th style="background:#10B981;color:#FFF;padding:8px 10px;font-size:10px;text-transform:uppercase;text-align:left;">Date</th>
                <th style="background:#10B981;color:#FFF;padding:8px 10px;font-size:10px;text-transform:uppercase;text-align:left;">Période</th>
                <th style="background:#10B981;color:#FFF;padding:8px 10px;font-size:10px;text-transform:uppercase;text-align:right;">Montant (F)</th>
                <th style="background:#10B981;color:#FFF;padding:8px 10px;font-size:10px;text-transform:uppercase;text-align:left;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contract->payments as $payment)
            <tr>
                <td style="padding:6px 10px;border-bottom:1px solid #E5E7EB;">{{ $payment->created_at->format('d/m/Y') }}</td>
                <td style="padding:6px 10px;border-bottom:1px solid #E5E7EB;">{{ $payment->period ?? '—' }}</td>
                <td style="padding:6px 10px;border-bottom:1px solid #E5E7EB;text-align:right;">{{ number_format($payment->amount, 0, ',', ' ') }}</td>
                <td style="padding:6px 10px;border-bottom:1px solid #E5E7EB;">{{ $payment->status ?? 'Payé' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($contract->notes)
    <div style="margin-top:10px;font-size:10px;color:#6B7280;"><strong>Notes :</strong> {{ $contract->notes }}</div>
    @endif

    <!-- Signatures -->
    <div style="margin-top:40px;">
        <table style="border:none;">
            <tr>
                <td style="border:none;padding:0;width:50%;text-align:center;">
                    <div style="font-size:10px;font-weight:600;color:#6B7280;margin-bottom:40px;">Le Bailleur</div>
                    <div style="border-top:1px solid #D1D5DB;display:inline-block;width:160px;padding-top:4px;font-size:9px;color:#9CA3AF;">Signature & cachet</div>
                </td>
                <td style="border:none;padding:0;width:50%;text-align:center;">
                    <div style="font-size:10px;font-weight:600;color:#6B7280;margin-bottom:40px;">Le Locataire</div>
                    <div style="border-top:1px solid #D1D5DB;display:inline-block;width:160px;padding-top:4px;font-size:9px;color:#9CA3AF;">Signature</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ $company->name }} — Contrat généré automatiquement par Khayma
    </div>
</body>
</html>
