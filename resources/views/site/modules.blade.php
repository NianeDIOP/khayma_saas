@extends('layouts.site')

@section('title', 'Modules — Khayma')
@section('description', 'Découvrez les modules Khayma : Restaurant, Boutique, Quincaillerie, Location. Chaque module s\'adapte à votre activité.')

@section('styles')
<style>
    .modules-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; padding: 4rem 0; }
    .module-card { background: var(--white); border: 1px solid #E2E8F0; padding: 2rem; transition: transform 0.2s, box-shadow 0.2s; }
    .module-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
    .module-icon { width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; background: var(--green-light); color: var(--green); font-size: 1.5rem; margin-bottom: 1.25rem; }
    .module-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; }
    .module-card p { color: var(--gray); font-size: 0.9rem; line-height: 1.6; }
    .module-features { list-style: none; margin-top: 1rem; }
    .module-features li { padding: 0.4rem 0; font-size: 0.85rem; color: var(--dark-3); display: flex; align-items: center; gap: 0.5rem; }
    .module-features li i { color: var(--green); font-size: 0.75rem; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="section-header" style="margin-bottom:0">
        <span class="section-badge green">Modules</span>
        <h1 class="section-title" style="color:var(--white)">Chaque module, <span class="accent-green">taillé</span> pour votre activité</h1>
        <p class="section-sub" style="color:rgba(255,255,255,0.5)">Restaurant, boutique, quincaillerie ou location — choisissez le module adapté à votre métier.</p>
    </div>
</div>

<div class="page-content">
    <div class="section-container" style="max-width:1280px">
        <div class="modules-grid">
            @forelse($modules as $module)
                <div class="module-card">
                    <div class="module-icon">
                        <i class="{{ $module->icon ?? 'fas fa-cube' }}"></i>
                    </div>
                    <h3>{{ $module->name }}</h3>
                    <p>{{ $module->description }}</p>
                    @if($module->installation_fee > 0)
                        <p style="margin-top:0.75rem;font-weight:700;color:var(--green)">
                            {{ number_format($module->installation_fee, 0, ',', ' ') }} XOF / installation
                        </p>
                    @endif
                </div>
            @empty
                <p style="color:var(--gray);grid-column:1/-1;text-align:center;padding:3rem 0">Les modules seront bientôt disponibles.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
