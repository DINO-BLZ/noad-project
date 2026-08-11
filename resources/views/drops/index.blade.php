@extends('layouts.app')

@section('content')
<div class="drops">
    <h1>Drops</h1>
    <p>Découvrez nos prochains lancements et éditions limitées.</p>

    <div class="drops__grid">
        @forelse($drops as $drop)
            <a href="{{ route('drops.show', $drop) }}" class="drop-card">
                <div class="drop-card__header">
                    <h2>{{ $drop->name }}</h2>
                    <span class="drop-card__status {{ $drop->status }}">{{ ucfirst($drop->status) }}</span>
                </div>
                <p>{{ \Illuminate\Support\Str::limit($drop->description, 120) }}</p>
                <p class="drop-card__meta">Produits : {{ $drop->products_count }} • Whitelist : {{ $drop->approved_whitelists_count }}</p>
            </a>
        @empty
            <p>Aucun drop disponible pour le moment.</p>
        @endforelse
    </div>
</div>

<style>
.drops{
    padding:3rem;
}
.drops__grid{
    display:grid;
    gap:1.5rem;
    margin-top:2rem;
}

.drop-card{
    display:block;
    padding:1.5rem;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.08);
    border-radius:1rem;
    text-decoration:none;
    color:inherit;
    transition:.2s;
}

.drop-card:hover{
    transform:translateY(-2px);
    border-color:var(--accent);
}

.drop-card__header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:1rem;
    margin-bottom:.75rem;
}

.drop-card__status{
    padding:.35rem .75rem;
    border-radius:999px;
    font-size:.75rem;
    text-transform:uppercase;
    font-weight:700;
}

.drop-card__status.active{
    background:rgba(176,46,38,.15);
    color:var(--accent);
}

.drop-card__status.upcoming{
    background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.85);
}

.drop-card__status.ended{
    background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.6);
}

.drop-card__meta{
    margin-top:1rem;
    font-size:.85rem;
    opacity:.7;
}
</style>
@endsection
