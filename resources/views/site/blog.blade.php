@extends('layouts.site')

@section('title', 'Blog — Khayma')
@section('description', 'Articles, guides et actualités sur la gestion commerciale en Afrique de l\'Ouest.')

@section('styles')
<style>
    .blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; padding: 4rem 0; }
    .blog-card { background: var(--white); border: 1px solid #E2E8F0; transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
    .blog-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
    .blog-card-img { width: 100%; height: 200px; background: var(--light); display: flex; align-items: center; justify-content: center; color: var(--gray-light); font-size: 2.5rem; }
    .blog-card-body { padding: 1.5rem; }
    .blog-card-meta { display: flex; align-items: center; gap: 1rem; font-size: 0.78rem; color: var(--gray); margin-bottom: 0.75rem; }
    .blog-card-category { background: var(--green-light); color: var(--green-dark); padding: 0.2rem 0.6rem; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .blog-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.4; }
    .blog-card h3 a { transition: color 0.2s; }
    .blog-card h3 a:hover { color: var(--green); }
    .blog-card p { color: var(--gray); font-size: 0.88rem; line-height: 1.6; }
    .blog-card-footer { padding: 1rem 1.5rem; border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between; }
    .blog-card-author { font-size: 0.8rem; color: var(--gray); }
    .blog-card-link { font-size: 0.85rem; font-weight: 600; color: var(--green); transition: color 0.2s; }
    .blog-card-link:hover { color: var(--green-dark); }
    .blog-pagination { display: flex; justify-content: center; gap: 0.5rem; padding-bottom: 4rem; }
    .blog-pagination a, .blog-pagination span { padding: 0.5rem 1rem; font-size: 0.85rem; border: 1px solid #E2E8F0; color: var(--gray); transition: all 0.2s; }
    .blog-pagination span.current { background: var(--green); color: var(--white); border-color: var(--green); }
    .blog-pagination a:hover { border-color: var(--green); color: var(--green); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="section-header" style="margin-bottom:0">
        <span class="section-badge orange">Blog</span>
        <h1 class="section-title" style="color:var(--white)">Actualités & <span class="accent-green">guides</span></h1>
        <p class="section-sub" style="color:rgba(255,255,255,0.5)">Conseils, tutoriels et actualités pour les entrepreneurs.</p>
    </div>
</div>

<div class="page-content">
    <div class="section-container" style="max-width:1280px">
        <div class="blog-grid">
            @forelse($posts as $post)
                <article class="blog-card">
                    <div class="blog-card-img">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-card-meta">
                            @if($post->category)
                                <span class="blog-card-category">{{ $post->category }}</span>
                            @endif
                            <span>{{ $post->published_at->format('d M Y') }}</span>
                        </div>
                        <h3><a href="{{ route('site.blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                        <p>{{ Str::limit($post->excerpt ?? strip_tags($post->body), 120) }}</p>
                    </div>
                    <div class="blog-card-footer">
                        <span class="blog-card-author">{{ $post->author?->name }}</span>
                        <a href="{{ route('site.blog.show', $post->slug) }}" class="blog-card-link">Lire &rarr;</a>
                    </div>
                </article>
            @empty
                <p style="text-align:center;color:var(--gray);grid-column:1/-1;padding:3rem 0">Aucun article pour le moment. Revenez bientôt !</p>
            @endforelse
        </div>

        @if($posts->hasPages())
            <div class="blog-pagination">
                {!! $posts->links('pagination::simple-default') !!}
            </div>
        @endif
    </div>
</div>
@endsection
