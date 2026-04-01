@extends('layouts.site')

@section('title', $blogPost->title . ' — Khayma Blog')
@section('description', Str::limit($blogPost->excerpt ?? strip_tags($blogPost->body), 160))

@section('styles')
<style>
    .article-header { background: var(--dark); padding: 7rem 1.5rem 3.5rem; }
    .article-header-inner { max-width: 800px; margin: 0 auto; }
    .article-meta { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .article-meta span { font-size: 0.8rem; color: rgba(255,255,255,0.5); }
    .article-category { background: rgba(16,185,129,0.15); color: var(--green); padding: 0.2rem 0.75rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .article-title { font-size: clamp(1.75rem, 3.5vw, 2.5rem); font-weight: 800; color: var(--white); letter-spacing: -0.04em; line-height: 1.2; }
    .article-body { max-width: 800px; margin: 0 auto; padding: 3rem 1.5rem 4rem; font-size: 1.05rem; line-height: 1.8; color: var(--dark-3); }
    .article-body h2 { font-size: 1.5rem; font-weight: 700; color: var(--dark); margin: 2.5rem 0 1rem; }
    .article-body h3 { font-size: 1.2rem; font-weight: 700; color: var(--dark); margin: 2rem 0 0.75rem; }
    .article-body p { margin-bottom: 1.25rem; }
    .article-body ul, .article-body ol { margin-left: 1.5rem; margin-bottom: 1.25rem; }
    .article-body li { margin-bottom: 0.5rem; }
    .article-body blockquote { border-left: 4px solid var(--green); padding: 1rem 1.5rem; margin: 1.5rem 0; background: var(--light); color: var(--gray); font-style: italic; }
    .article-back { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--green); font-weight: 600; font-size: 0.9rem; margin-bottom: 2rem; transition: color 0.2s; }
    .article-back:hover { color: var(--green-dark); }
</style>
@endsection

@section('content')
<div class="article-header">
    <div class="article-header-inner">
        <div class="article-meta">
            @if($blogPost->category)
                <span class="article-category">{{ $blogPost->category }}</span>
            @endif
            <span>{{ $blogPost->published_at?->format('d M Y') }}</span>
            <span>Par {{ $blogPost->author?->name }}</span>
        </div>
        <h1 class="article-title">{{ $blogPost->title }}</h1>
    </div>
</div>

<div class="article-body">
    <a href="{{ route('site.blog') }}" class="article-back">
        <i class="fas fa-arrow-left"></i> Retour au blog
    </a>
    {!! $blogPost->body !!}
</div>
@endsection
