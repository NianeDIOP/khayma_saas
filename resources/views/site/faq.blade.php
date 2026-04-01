@extends('layouts.site')

@section('title', 'FAQ — Khayma')
@section('description', 'Questions fréquentes sur Khayma. Trouvez des réponses sur nos modules, tarifs et fonctionnalités.')

@section('styles')
<style>
    .faq-filters { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center; margin-bottom: 3rem; }
    .faq-filter { padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight: 600; border: 1px solid #E2E8F0; background: var(--white); color: var(--gray); cursor: pointer; font-family: var(--font); transition: all 0.2s; }
    .faq-filter.active { background: var(--green); color: var(--white); border-color: var(--green); }
    .faq-filter:hover { border-color: var(--green); }
    .faq-list { max-width: 800px; margin: 0 auto; }
    .faq-item { border: 1px solid #E2E8F0; margin-bottom: 0.75rem; background: var(--white); }
    .faq-question { width: 100%; padding: 1.25rem 1.5rem; font-size: 0.95rem; font-weight: 600; text-align: left; background: none; border: none; cursor: pointer; font-family: var(--font); display: flex; align-items: center; justify-content: space-between; gap: 1rem; color: var(--dark); transition: color 0.2s; }
    .faq-question:hover { color: var(--green); }
    .faq-question i { transition: transform 0.3s; flex-shrink: 0; color: var(--gray-light); }
    .faq-item.open .faq-question i { transform: rotate(180deg); color: var(--green); }
    .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
    .faq-answer-inner { padding: 0 1.5rem 1.25rem; color: var(--gray); font-size: 0.9rem; line-height: 1.7; }
    .faq-item.open .faq-answer { max-height: 500px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="section-header" style="margin-bottom:0">
        <span class="section-badge green">FAQ</span>
        <h1 class="section-title" style="color:var(--white)">Questions <span class="accent-green">fréquentes</span></h1>
        <p class="section-sub" style="color:rgba(255,255,255,0.5)">Trouvez rapidement des réponses à vos questions.</p>
    </div>
</div>

<div class="page-content">
    <div class="section-container">
        @if($categories->isNotEmpty())
            <div class="faq-filters">
                <button class="faq-filter active" onclick="filterFaq('all', this)">Toutes</button>
                @foreach($categories as $cat)
                    <button class="faq-filter" onclick="filterFaq('{{ e($cat) }}', this)">{{ $cat }}</button>
                @endforeach
            </div>
        @endif

        <div class="faq-list">
            @forelse($faqs as $faq)
                <div class="faq-item" data-category="{{ $faq->category }}">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        <span>{{ $faq->question }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">{!! nl2br(e($faq->answer)) !!}</div>
                    </div>
                </div>
            @empty
                <p style="text-align:center;color:var(--gray);padding:3rem 0">Aucune FAQ pour le moment.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    item.classList.toggle('open');
}

function filterFaq(category, btn) {
    document.querySelectorAll('.faq-filter').forEach(f => f.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.faq-item').forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
@endsection
