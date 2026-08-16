@extends('layouts.app')

@section('content')
<div class="whitelist-page">
    <h1>Mes demandes de whitelist</h1>

    @if(session('success'))
        <p class="whitelist-page__notice">{{ session('success') }}</p>
    @endif

    @forelse($whitelists as $w)
        <div class="whitelist-page__row">
            <div>
                <h3>{{ $w->drop->name }}</h3>
                <p>Demandée le {{ $w->created_at->format('d M Y') }}</p>
            </div>

            <span class="drop-status drop-status--{{ $w->status === 'approved' ? 'active' : ($w->status === 'rejected' ? 'ended' : 'upcoming') }}">
                {{ match($w->status) {
                    'approved' => 'Acceptée',
                    'rejected' => 'Refusée',
                    default => 'En attente',
                } }}
            </span>

            <a href="{{ route('drops.show', $w->drop) }}">Voir le drop</a>
        </div>
    @empty
        <p class="whitelist-page__empty">Vous n'avez fait aucune demande pour le moment.</p>
    @endforelse
</div>

<style>
.whitelist-page{ padding:3rem; max-width:800px; margin:0 auto; }
.whitelist-page h1{ font-size:1.6rem; font-weight:900; text-transform:uppercase; margin-bottom:2rem; }
.whitelist-page__row{
    display:flex; justify-content:space-between; align-items:center;
    padding:1.2rem 0; border-bottom:1px solid var(--border);
}
.whitelist-page__row h3{ font-size:1rem; text-transform:uppercase; }
.whitelist-page__row p{ font-size:.8rem; opacity:.6; margin-top:.2rem; }
.whitelist-page__row a{ color:var(--accent); text-decoration:none; font-size:.85rem; }
.whitelist-page__empty{ opacity:.5; }
.whitelist-page__notice{ margin-bottom:1.5rem; color:#2ecc71; }
</style>
@endsection