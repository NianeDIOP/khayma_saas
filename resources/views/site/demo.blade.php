@extends('layouts.site')

@section('title', 'Démo — Khayma')
@section('description', 'Essayez Khayma sans inscription. Accédez à une démo interactive avec des données fictives.')

@section('styles')
<style>
    .demo-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem; max-width: 1100px; margin: 0 auto; padding: 4rem 0; }
    .demo-card { background: var(--white); border: 1px solid #E2E8F0; padding: 2.5rem 2rem; text-align: center; transition: transform 0.2s, box-shadow 0.2s; }
    .demo-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
    .demo-card-icon { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 1.75rem; }
    .demo-card-icon.restaurant { background: rgba(239,68,68,0.1); color: #EF4444; }
    .demo-card-icon.boutique { background: rgba(139,92,246,0.1); color: #8B5CF6; }
    .demo-card-icon.quincaillerie { background: rgba(245,158,11,0.1); color: #F59E0B; }
    .demo-card-icon.location { background: rgba(59,130,246,0.1); color: #3B82F6; }
    .demo-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; }
    .demo-card p { color: var(--gray); font-size: 0.9rem; margin-bottom: 1.5rem; }
    .demo-btn { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--green); color: var(--white); padding: 0.7rem 1.5rem; font-size: 0.9rem; font-weight: 600; border: none; cursor: pointer; font-family: var(--font); transition: background 0.2s; }
    .demo-btn:hover { background: var(--green-dark); }
    .demo-note { text-align: center; color: var(--gray); font-size: 0.85rem; max-width: 600px; margin: 0 auto; padding-bottom: 4rem; }
    .demo-note i { color: var(--green); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="section-header" style="margin-bottom:0">
        <span class="section-badge orange">Démo</span>
        <h1 class="section-title" style="color:var(--white)">Testez Khayma <span class="accent-green">sans inscription</span></h1>
        <p class="section-sub" style="color:rgba(255,255,255,0.5)">Explorez chaque module avec des données fictives. Aucun engagement nécessaire.</p>
    </div>
</div>

<div class="page-content">
    <div class="section-container" style="max-width:1280px">
        <div class="demo-cards">
            <div class="demo-card">
                <div class="demo-card-icon restaurant"><i class="fas fa-utensils"></i></div>
                <h3>Restaurant / Snack</h3>
                <p>Commandes, cuisine, caisse, gestion des plats et catégories.</p>
                <a href="{{ route('login') }}" class="demo-btn"><i class="fas fa-play"></i> Lancer la démo</a>
            </div>
            <div class="demo-card">
                <div class="demo-card-icon boutique"><i class="fas fa-shirt"></i></div>
                <h3>Boutique / Prêt-à-porter</h3>
                <p>Ventes, variantes, promotions, fidélité et multi-dépôts.</p>
                <a href="{{ route('login') }}" class="demo-btn"><i class="fas fa-play"></i> Lancer la démo</a>
            </div>
            <div class="demo-card">
                <div class="demo-card-icon quincaillerie"><i class="fas fa-wrench"></i></div>
                <h3>Quincaillerie / Matériaux</h3>
                <p>Devis, bons de commande, fournisseurs, inventaires.</p>
                <a href="{{ route('login') }}" class="demo-btn"><i class="fas fa-play"></i> Lancer la démo</a>
            </div>
            <div class="demo-card">
                <div class="demo-card-icon location"><i class="fas fa-car"></i></div>
                <h3>Location de véhicules</h3>
                <p>Contrats, actifs, paiements, calendrier et rappels.</p>
                <a href="{{ route('login') }}" class="demo-btn"><i class="fas fa-play"></i> Lancer la démo</a>
            </div>
        </div>
        <div class="demo-note">
            <p><i class="fas fa-info-circle"></i> Les comptes démo utilisent des données fictives qui sont réinitialisées régulièrement. Aucune donnée n'est conservée.</p>
        </div>
    </div>
</div>
@endsection
