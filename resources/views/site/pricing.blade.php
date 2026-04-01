@extends('layouts.site')

@section('title', 'Tarifs — Khayma')
@section('description', 'Découvrez nos plans et tarifs. Essai gratuit de 7 jours, sans engagement.')

@section('styles')
<style>
    .pricing-toggle { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 3rem; }
    .pricing-toggle span { font-size: 0.9rem; font-weight: 600; color: var(--gray); cursor: pointer; transition: color 0.2s; }
    .pricing-toggle span.active { color: var(--dark); }
    .toggle-switch { width: 48px; height: 26px; background: var(--gray-light); border-radius: 13px; position: relative; cursor: pointer; transition: background 0.3s; }
    .toggle-switch.on { background: var(--green); }
    .toggle-switch::after { content: ''; position: absolute; width: 20px; height: 20px; background: var(--white); border-radius: 50%; top: 3px; left: 3px; transition: transform 0.3s; }
    .toggle-switch.on::after { transform: translateX(22px); }

    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; max-width: 1100px; margin: 0 auto; }
    .plan-card { background: var(--white); border: 2px solid #E2E8F0; padding: 2.5rem 2rem; text-align: center; transition: transform 0.2s, box-shadow 0.2s; position: relative; }
    .plan-card.featured { border-color: var(--green); }
    .plan-card.featured::before { content: 'Populaire'; position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--green); color: var(--white); font-size: 0.7rem; font-weight: 700; padding: 0.25rem 1rem; text-transform: uppercase; letter-spacing: 0.08em; }
    .plan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
    .plan-name { font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; }
    .plan-price { margin-bottom: 1.5rem; }
    .plan-amount { font-size: 2.5rem; font-weight: 900; letter-spacing: -0.04em; }
    .plan-period { font-size: 0.85rem; color: var(--gray); }
    .plan-features { list-style: none; text-align: left; margin-bottom: 2rem; }
    .plan-features li { padding: 0.5rem 0; font-size: 0.88rem; color: var(--dark-3); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #F1F5F9; }
    .plan-features li i { color: var(--green); font-size: 0.8rem; width: 16px; text-align: center; }
    .plan-btn { display: block; width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 700; border: none; cursor: pointer; font-family: var(--font); transition: all 0.2s; }
    .plan-btn-primary { background: var(--green); color: var(--white); }
    .plan-btn-primary:hover { background: var(--green-dark); }
    .plan-btn-outline { background: transparent; color: var(--dark); border: 2px solid #E2E8F0; }
    .plan-btn-outline:hover { border-color: var(--green); color: var(--green); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="section-header" style="margin-bottom:0">
        <span class="section-badge green">Tarifs</span>
        <h1 class="section-title" style="color:var(--white)">Des prix <span class="accent-green">transparents</span> et accessibles</h1>
        <p class="section-sub" style="color:rgba(255,255,255,0.5)">Essai gratuit de 7 jours. Aucune carte bancaire requise.</p>
    </div>
</div>

<div class="page-content">
    <div class="section-container" style="max-width:1280px">
        <div class="pricing-toggle">
            <span id="lblMonthly" class="active">Mensuel</span>
            <div class="toggle-switch" id="toggleSwitch" onclick="togglePricing()"></div>
            <span id="lblYearly">Annuel <span style="color:var(--green);font-size:0.75rem">(-20%)</span></span>
        </div>

        <div class="plans-grid">
            @foreach($plans as $index => $plan)
                <div class="plan-card {{ $index === 1 ? 'featured' : '' }}">
                    <div class="plan-name">{{ $plan->name }}</div>
                    <div class="plan-price">
                        <span class="plan-amount"
                              data-monthly="{{ number_format($plan->price_monthly, 0, ',', ' ') }}"
                              data-yearly="{{ number_format($plan->price_yearly, 0, ',', ' ') }}">
                            {{ number_format($plan->price_monthly, 0, ',', ' ') }}
                        </span>
                        <span class="plan-period">XOF / <span class="period-text">mois</span></span>
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> {{ $plan->max_products ?: '∞' }} produits</li>
                        <li><i class="fas fa-check"></i> {{ $plan->max_users }} utilisateurs</li>
                        <li><i class="fas fa-check"></i> {{ $plan->max_storage_gb }} Go stockage</li>
                        <li><i class="fas fa-check"></i> {{ $plan->max_transactions_month ?: '∞' }} transactions/mois</li>
                    </ul>
                    <a href="{{ route('register') }}" class="plan-btn {{ $index === 1 ? 'plan-btn-primary' : 'plan-btn-outline' }}">
                        Commencer l'essai gratuit
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let isYearly = false;
    function togglePricing() {
        isYearly = !isYearly;
        document.getElementById('toggleSwitch').classList.toggle('on', isYearly);
        document.getElementById('lblMonthly').classList.toggle('active', !isYearly);
        document.getElementById('lblYearly').classList.toggle('active', isYearly);
        document.querySelectorAll('.plan-amount').forEach(el => {
            el.textContent = isYearly ? el.dataset.yearly : el.dataset.monthly;
        });
        document.querySelectorAll('.period-text').forEach(el => {
            el.textContent = isYearly ? 'an' : 'mois';
        });
    }
</script>
@endsection
