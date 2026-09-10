@extends('layouts.admin')

@section('content')
<div class="admin-whitelist">
    <h1>Demandes de whitelist</h1>

    <div class="admin-whitelist__filters">
        <a href="{{ route('admin.whitelist.index') }}" class="{{ !request('status') ? 'is-active' : '' }}">Toutes</a>
        <a href="{{ route('admin.whitelist.index', ['status' => 'pending']) }}" class="{{ request('status') === 'pending' ? 'is-active' : '' }}">En attente</a>
        <a href="{{ route('admin.whitelist.index', ['status' => 'approved']) }}" class="{{ request('status') === 'approved' ? 'is-active' : '' }}">Acceptées</a>
        <a href="{{ route('admin.whitelist.index', ['status' => 'rejected']) }}" class="{{ request('status') === 'rejected' ? 'is-active' : '' }}">Refusées</a>
        <a href="{{ route('admin.whitelist.index', ['status' => 'expired']) }}" class="{{ request('status') === 'expired' ? 'is-active' : '' }}">Expirées</a>
    </div>

    @if(session('success'))
        <p class="admin-notice">{{ session('success') }}</p>
    @endif

    @forelse($whitelists as $req)
        <div class="whitelist-row">
            <span>{{ $req->user->name }} ({{ $req->user->email }})</span>
            <span>{{ $req->drop->name }}</span>
            <x-status-badge :status="$req->status" />

            @if($req->status === 'pending')
                <div class="whitelist-row__actions">
                    <form action="{{ route('admin.drops.whitelist.approve', [$req->drop, $req->id]) }}" method="POST">
                        @csrf
                        <button type="submit">Approuver</button>
                    </form>
                    <form action="{{ route('admin.drops.whitelist.reject', [$req->drop, $req->id]) }}" method="POST">
                        @csrf
                        <button type="submit">Refuser</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <p class="admin-panel__empty">Aucune demande.</p>
    @endforelse
</div>

<style>
.admin-whitelist{ padding:3rem; max-width:1000px; margin:0 auto; }
.admin-whitelist h1{ font-size:1.6rem; font-weight:900; text-transform:uppercase; margin-bottom:1.5rem; }
.admin-whitelist__filters{ display:flex; gap:1rem; margin-bottom:1.5rem; }
.admin-whitelist__filters a{ color:var(--text); opacity:.5; text-decoration:none; font-size:.85rem; text-transform:uppercase; }
.admin-whitelist__filters a.is-active{ opacity:1; color:var(--accent); font-weight:700; }
</style>
@endsection