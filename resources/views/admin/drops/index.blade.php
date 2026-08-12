@extends('layouts.app')

@section('content')
<div class="admin-drops">

    <div class="admin-drops__header">
        <h1>Drops</h1>
        <a href="{{ route('admin.drops.create') }}" class="admin-btn">+ Créer un drop</a>
    </div>

    @if(session('success'))
        <p class="admin-notice">{{ session('success') }}</p>
    @endif

    <div class="admin-drops__list">
        @forelse($drops as $drop)
            <div class="admin-drop-row">
                <div>
                    <h3>{{ $drop->name }}</h3>
                    <p>{{ $drop->start_date->format('d/m/Y H:i') }} → {{ $drop->end_date->format('d/m/Y H:i') }}</p>
                </div>
                <span class="drop-status drop-status--{{ $drop->status }}">{{ ucfirst($drop->status) }}</span>
                <div class="admin-drop-row__actions">
                    <a href="{{ route('admin.drops.edit', $drop) }}">Gérer</a>
                    <form action="{{ route('admin.drops.destroy', $drop) }}" method="POST" onsubmit="return confirm('Supprimer ce drop ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="admin-panel__empty">Aucun drop créé pour le moment.</p>
        @endforelse
    </div>

</div>

<style>
.admin-drops{
    padding:3rem;
    max-width:1000px;
    margin:0 auto;
}

.admin-drops__header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:2rem;
}

.admin-drops__header h1{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
}

.admin-btn{
    background:var(--accent);
    color:#fff;
    text-decoration:none;
    padding:.8rem 1.4rem;
    font-size:.85rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.admin-notice{
    background:rgba(46,204,113,.1);
    border:1px solid #2ecc71;
    color:#2ecc71;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    font-size:.85rem;
}

.admin-drop-row{
    display:grid;
    grid-template-columns:1fr auto auto;
    align-items:center;
    gap:1.5rem;
    padding:1.2rem 0;
    border-bottom:1px solid var(--border);
}

.admin-drop-row h3{
    font-size:.95rem;
    text-transform:uppercase;
}

.admin-drop-row p{
    font-size:.8rem;
    opacity:.7;
    margin-top:.2rem;
}

.drop-status{
    font-size:.7rem;
    text-transform:uppercase;
    padding:.3rem .8rem;
    border:1px solid var(--border);
}

.drop-status--upcoming{ color:#3498db; border-color:#3498db; }
.drop-status--active{ color:#2ecc71; border-color:#2ecc71; }
.drop-status--ended{ color:var(--accent); border-color:var(--accent); }

.admin-drop-row__actions{
    display:flex;
    gap:.8rem;
}

.admin-drop-row__actions a,
.admin-drop-row__actions button{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    font-size:.75rem;
    text-transform:uppercase;
    padding:.5rem 1rem;
    cursor:pointer;
    text-decoration:none;
}

.admin-drop-row__actions button:hover{
    background:var(--accent);
    border-color:var(--accent);
    color:#fff;
}

.admin-panel__empty{
    opacity:.5;
    padding:2rem 0;
}
</style>
@endsection