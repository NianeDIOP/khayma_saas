@extends('layouts.site')

@section('title', $title . ' — Khayma')

@section('styles')
<style>
    .legal-body { max-width: 800px; margin: 0 auto; padding: 3rem 1.5rem 4rem; font-size: 1rem; line-height: 1.8; color: var(--dark-3); }
    .legal-body h2 { font-size: 1.4rem; font-weight: 700; color: var(--dark); margin: 2rem 0 0.75rem; }
    .legal-body h3 { font-size: 1.15rem; font-weight: 700; color: var(--dark); margin: 1.5rem 0 0.5rem; }
    .legal-body p { margin-bottom: 1rem; }
    .legal-body ul, .legal-body ol { margin-left: 1.5rem; margin-bottom: 1rem; }
    .legal-body li { margin-bottom: 0.4rem; }
    .legal-empty { text-align: center; color: var(--gray); padding: 4rem 0; font-size: 0.95rem; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="section-header" style="margin-bottom:0">
        <h1 class="section-title" style="color:var(--white)">{{ $title }}</h1>
    </div>
</div>

<div class="legal-body">
    @if($content)
        {!! $content !!}
    @else
        <div class="legal-empty">
            <i class="fas fa-file-alt" style="font-size:2.5rem;color:var(--gray-light);display:block;margin-bottom:1rem"></i>
            <p>Cette page sera bientôt disponible.</p>
        </div>
    @endif
</div>
@endsection
