@extends('layouts.app')

@section('title', 'Journal — NOAD')
@section('meta_description', 'Chroniques, culture terrace et manifeste de la marque NOAD.')

@section('content')

<div class="journal-index-page">

    {{-- BANDE TECHNIQUE --}}
    <div class="journal-top-strip">
        <div class="container-journal strip-flex font-mono">
            <div>
                <span class="dot-red">■</span>
                SYS.DIR: JOURNAL // ARCHIVES ÉDITORIALES
            </div>
            <div>
                {{ count($articles) }} ARTICLES ENREGISTRÉS
            </div>
        </div>
    </div>

    <div class="container-journal">

        {{-- BREADCRUMBS --}}
        <div class="breadcrumbs-row font-mono">
            <a href="{{ route('home') }}">ACCUEIL</a>
            <span class="sep">/</span>
            <span class="active">JOURNAL</span>
        </div>

        {{-- HEADER --}}
        <header class="journal-header">
            <span class="journal-kicker font-mono">CHRONIQUES & MANIFESTE</span>
            <h1 class="journal-title">LE JOURNAL NOAD</h1>
            <p class="journal-subtitle">
                Plongez dans l'univers de NOAD : nos principes fondamentaux, l'histoire des British Casuals
                et notre philosophie du vêtement conçu pour durer.
            </p>
        </header>

        {{-- GRILLE D'ARTICLES --}}
        <div class="journal-grid">
            @foreach($articles as $article)
                <article class="journal-full-card">
                    <div class="card-visual" style="background: {{ $article['fallback_gradient'] }};">
                        <span class="card-visual-tag font-mono">NOAD // ARCHIVE 0{{ $article['id'] }}</span>
                        <h2 class="card-visual-title font-mono">{{ $article['title'] }}</h2>
                    </div>

                    <div class="card-content">
                        <div class="card-meta font-mono">
                            <span class="card-category">{{ $article['category'] }}</span>
                            <span class="meta-sep">•</span>
                            <span class="card-date">{{ $article['date'] }}</span>
                            <span class="meta-sep">•</span>
                            <span class="card-time">{{ $article['read_time'] }}</span>
                        </div>

                        <h3 class="card-heading">
                            <a href="{{ route('journal.show', $article['id']) }}">
                                {{ $article['title'] }}
                            </a>
                        </h3>

                        <p class="card-excerpt">
                            {{ $article['subtitle'] }}
                        </p>

                        <div class="card-footer">
                            <a href="{{ route('journal.show', $article['id']) }}" class="btn-read font-mono">
                                LIRE L'HISTOIRE COMPLÈTE →
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

    </div>

</div>

<style>
.journal-index-page {
    min-height: 85vh;
    padding-bottom: 5rem;
    color: var(--text);
    background: var(--bg);
}

.container-journal {
    max-width: 1100px;
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
    margin: 2rem 0;
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

.journal-header {
    border-bottom: 1px solid var(--border);
    padding-bottom: 2.5rem;
    margin-bottom: 3rem;
}

.journal-kicker {
    display: block;
    font-size: 0.75rem;
    letter-spacing: 0.15em;
    color: var(--accent);
    margin-bottom: 0.75rem;
}

.journal-title {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    margin: 0 0 1rem;
    letter-spacing: -0.02em;
    text-transform: uppercase;
}

.journal-subtitle {
    font-size: 1.05rem;
    color: rgba(245, 245, 245, 0.7);
    line-height: 1.6;
    max-width: 650px;
    margin: 0;
}

.journal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}

.journal-full-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    transition: all 0.25s ease;
}

.journal-full-card:hover {
    border-color: var(--accent);
    transform: translateY(-3px);
}

.card-visual {
    height: 200px;
    border-bottom: 1px solid var(--border);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.card-visual-tag {
    font-size: 0.65rem;
    letter-spacing: 0.15em;
    color: var(--accent);
    margin-bottom: 0.4rem;
}

.card-visual-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #fff;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.card-content {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.card-meta {
    font-size: 0.68rem;
    color: rgba(245, 245, 245, 0.45);
    display: flex;
    gap: 0.45rem;
    align-items: center;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.card-category {
    color: var(--accent);
}

.card-heading {
    font-size: 1.3rem;
    font-weight: 800;
    margin: 0 0 0.85rem;
    line-height: 1.25;
}

.card-heading a {
    color: var(--text);
    text-decoration: none;
    transition: color 0.2s;
}

.card-heading a:hover {
    color: var(--accent);
}

.card-excerpt {
    font-size: 0.9rem;
    color: rgba(245, 245, 245, 0.65);
    line-height: 1.6;
    margin: 0 0 1.5rem;
    flex: 1;
}

.card-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    padding-top: 1rem;
}

.btn-read {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: #fff;
    text-decoration: none;
    transition: color 0.2s;
}

.btn-read:hover {
    color: var(--accent);
}
</style>
@endsection
