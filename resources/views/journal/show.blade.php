@extends('layouts.app')

@section('title', $article['title'] . ' — Journal NOAD')
@section('meta_description', Str::limit($article['subtitle'], 160))

@section('content')

<article class="journal-single-page">

    {{-- BANDE TECHNIQUE HAUT --}}
    <div class="journal-top-strip">
        <div class="container-article strip-flex font-mono">
            <div>
                <span class="dot-red">■</span>
                SYS.ARCHIVE // NOAD JOURNAL EDITION
            </div>
            <div>
                DATE: {{ $article['date'] }} // {{ $article['read_time'] }}
            </div>
        </div>
    </div>

    <div class="container-article">

        {{-- BREADCRUMBS --}}
        <div class="breadcrumbs-row font-mono">
            <a href="{{ route('home') }}">ACCUEIL</a>
            <span class="sep">/</span>
            <a href="{{ route('journal.index') }}">JOURNAL</a>
            <span class="sep">/</span>
            <span class="active">{{ strtoupper($article['title']) }}</span>
        </div>

        {{-- EN-TÊTE DE L'ARTICLE --}}
        <header class="article-header">
            <span class="article-category font-mono">
                {{ $article['category'] }}
            </span>

            <h1 class="article-main-title">
                {{ $article['title'] }}
            </h1>

            <p class="article-lead">
                {{ $article['subtitle'] }}
            </p>

            <div class="article-meta-bar font-mono">
                <div class="meta-item">
                    <span class="meta-lbl">PUBLICATION :</span>
                    <span class="meta-val">{{ $article['date'] }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-lbl">AUTEUR :</span>
                    <span class="meta-val">ATELIER NOAD</span>
                </div>
                <div class="meta-item">
                    <span class="meta-lbl">TEMPS :</span>
                    <span class="meta-val">{{ $article['read_time'] }}</span>
                </div>
            </div>
        </header>

        {{-- IMAGE / BANNIÈRE VISUELLE --}}
        <div class="article-hero-banner" style="background: {{ $article['fallback_gradient'] }};">
            <div class="banner-inner">
                <span class="banner-watermark font-mono">NOAD // ARCHIVE</span>
                <span class="banner-title font-mono">{{ $article['title'] }}</span>
            </div>
        </div>

        {{-- CITATION CLÉ (PULL-QUOTE) --}}
        @if(!empty($article['quote']))
            <blockquote class="article-pull-quote">
                <p>{{ $article['quote'] }}</p>
            </blockquote>
        @endif

        {{-- CONTENU PRINCIPAL --}}
        <div class="article-body">
            @foreach($article['sections'] as $section)
                <section class="article-section">
                    <h2 class="section-title font-mono">
                        {{ $section['heading'] }}
                    </h2>
                    <p class="section-text">
                        {{ $section['content'] }}
                    </p>
                </section>
            @endforeach
        </div>

        {{-- AUTRES ARTICLES / SUGGESTIONS --}}
        <aside class="other-articles-section">
            <div class="other-head font-mono">
                <span>00 // CONTINUER LA LECTURE</span>
                <span class="rule-line"></span>
            </div>

            <div class="other-grid">
                @foreach($otherArticles as $other)
                    <a href="{{ route('journal.show', $other['id']) }}" class="other-card">
                        <span class="other-date font-mono">{{ $other['date'] }}</span>
                        <h3 class="other-title">{{ $other['title'] }}</h3>
                        <p class="other-desc">{{ Str::limit($other['summary'], 100) }}</p>
                        <span class="other-link font-mono">LIRE L'ARTICLE →</span>
                    </a>
                @endforeach
            </div>
        </aside>

        {{-- RETOUR ACCUEIL --}}
        <div class="back-footer">
            <a href="{{ route('home') }}" class="btn-back-home font-mono">
                ← RETOUR À L'ACCUEIL
            </a>
        </div>

    </div>

</article>

<style>
.journal-single-page {
    min-height: 85vh;
    padding-bottom: 5rem;
    color: var(--text);
    background: var(--bg);
}

.container-article {
    max-width: 860px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.journal-top-strip {
    border-bottom: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.02);
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    padding: 0.6rem 0;
}

.strip-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dot-red {
    color: var(--accent);
    margin-right: 0.4rem;
}

.font-mono {
    font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
}

.breadcrumbs-row {
    font-size: 0.72rem;
    letter-spacing: 0.08em;
    color: rgba(245, 245, 245, 0.45);
    margin: 2rem 0 2rem;
}

.breadcrumbs-row a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumbs-row a:hover {
    color: var(--text);
}

.breadcrumbs-row .sep {
    margin: 0 0.5rem;
    opacity: 0.3;
}

.breadcrumbs-row .active {
    color: var(--accent);
}

.article-header {
    margin-bottom: 2.5rem;
    border-bottom: 1px solid var(--border);
    padding-bottom: 2rem;
}

.article-category {
    display: inline-block;
    font-size: 0.75rem;
    letter-spacing: 0.15em;
    color: var(--accent);
    margin-bottom: 1rem;
}

.article-main-title {
    font-size: clamp(2rem, 5vw, 3.2rem);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -0.02em;
    margin: 0 0 1.25rem;
    text-transform: uppercase;
}

.article-lead {
    font-size: 1.15rem;
    line-height: 1.6;
    color: rgba(245, 245, 245, 0.8);
    margin: 0 0 1.5rem;
}

.article-meta-bar {
    display: flex;
    gap: 2rem;
    font-size: 0.72rem;
    color: rgba(245, 245, 245, 0.45);
    letter-spacing: 0.08em;
    flex-wrap: wrap;
}

.meta-item .meta-val {
    color: var(--text);
    margin-left: 0.3rem;
}

/* BANNIÈRE */
.article-hero-banner {
    height: 320px;
    border: 1px solid var(--border);
    position: relative;
    display: flex;
    align-items: flex-end;
    padding: 2rem;
    margin-bottom: 3rem;
    overflow: hidden;
}

.banner-inner {
    position: relative;
    z-index: 2;
}

.banner-watermark {
    display: block;
    font-size: 0.75rem;
    letter-spacing: 0.2em;
    color: var(--accent);
    margin-bottom: 0.4rem;
}

.banner-title {
    font-size: 1.4rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: #fff;
    text-transform: uppercase;
}

/* CITATION */
.article-pull-quote {
    margin: 0 0 3rem;
    padding: 1.75rem 2rem;
    background: rgba(176, 46, 38, 0.05);
    border-left: 3px solid var(--accent);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
}

.article-pull-quote p {
    font-size: 1.15rem;
    font-style: italic;
    line-height: 1.6;
    color: rgba(245, 245, 245, 0.95);
    margin: 0;
}

/* CORPS DU TEXTE */
.article-body {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    margin-bottom: 4rem;
}

.article-section {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.section-title {
    font-size: 0.95rem;
    letter-spacing: 0.12em;
    color: var(--text);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 0.5rem;
    margin: 0;
}

.section-text {
    font-size: 1.05rem;
    line-height: 1.8;
    color: rgba(245, 245, 245, 0.75);
    margin: 0;
}

/* AUTRES ARTICLES */
.other-articles-section {
    border-top: 1px solid var(--border);
    padding-top: 2.5rem;
    margin-bottom: 3rem;
}

.other-head {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.75rem;
    letter-spacing: 0.12em;
    color: rgba(245, 245, 245, 0.45);
    margin-bottom: 1.5rem;
}

.other-head .rule-line {
    flex: 1;
    height: 1px;
    background: var(--border);
}

.other-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}

.other-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
}

.other-card:hover {
    border-color: var(--accent);
    background: rgba(255, 255, 255, 0.04);
    transform: translateY(-2px);
}

.other-date {
    font-size: 0.7rem;
    color: var(--accent);
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
}

.other-title {
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0 0 0.5rem;
    letter-spacing: 0.02em;
}

.other-desc {
    font-size: 0.85rem;
    color: rgba(245, 245, 245, 0.6);
    line-height: 1.5;
    margin: 0 0 1rem;
    flex: 1;
}

.other-link {
    font-size: 0.75rem;
    color: #fff;
    font-weight: 700;
    letter-spacing: 0.1em;
}

.back-footer {
    display: flex;
    justify-content: center;
    padding-top: 1rem;
}

.btn-back-home {
    display: inline-block;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 0.85rem 1.75rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-back-home:hover {
    border-color: var(--text);
    background: rgba(255, 255, 255, 0.04);
}
</style>
@endsection
