@extends('layouts.admin')

@section('content')
<div class="admin-form-page">

    <h1>Modifier le drop</h1>

    @if(session('success'))
        <p class="admin-notice">{{ session('success') }}</p>
    @endif

    <form action="{{ route('admin.drops.update', $drop) }}" method="POST" class="admin-form">
        @csrf
        @method('PUT')

        <label>Nom du drop
            <input type="text" name="name" value="{{ old('name', $drop->name) }}" required>
        </label>

        <label>Description
            <textarea name="description" rows="3">{{ old('description', $drop->description) }}</textarea>
        </label>

        <label>Date de début
            <input type="datetime-local" name="start_date" value="{{ old('start_date', $drop->start_date->format('Y-m-d\TH:i')) }}" required>
        </label>

        <label>Date de fin
            <input type="datetime-local" name="end_date" value="{{ old('end_date', $drop->end_date->format('Y-m-d\TH:i')) }}" required>
        </label>

        <label>Statut
            
        </label>

        <label>Produits associés
            <div class="checkbox-list">
                @foreach($products as $product)
                    <label class="checkbox-item">
                        <input type="checkbox" name="products[]" value="{{ $product->id }}"
                               {{ $drop->products->contains($product->id) ? 'checked' : '' }}>
                        {{ $product->name }}
                    </label>
                @endforeach
            </div>
        </label>

        @if($errors->any())
            <div class="admin-form__errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="submit" class="admin-btn">Mettre à jour</button>
    </form>

    <div class="whitelist-panel">
        <h2>Demandes de whitelist</h2>
        @forelse($whitelistRequests as $req)
            <div class="whitelist-row">
                <span>{{ $req->user->name }} ({{ $req->user->email }})</span>
               <x-status-badge :status="$req->status" />
                @if($req->status === 'pending')
                    <div class="whitelist-row__actions">
                        <form action="{{ route('admin.drops.whitelist.approve', [$drop, $req->id]) }}" method="POST">
                            @csrf
                            <button type="submit">Approuver</button>
                        </form>
                        <form action="{{ route('admin.drops.whitelist.reject', [$drop, $req->id]) }}" method="POST">
                            @csrf
                            <button type="submit">Refuser</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="admin-panel__empty">Aucune demande pour ce drop.</p>
        @endforelse
    </div>

</div>

<style>
.admin-form-page{
    padding:3rem;
    max-width:700px;
    margin:0 auto;
}

.admin-form-page h1{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:2rem;
}

.admin-notice{
    background:rgba(46,204,113,.1);
    border:1px solid #2ecc71;
    color:#2ecc71;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    font-size:.85rem;
}

.admin-form{
    display:flex;
    flex-direction:column;
    gap:1.2rem;
}

.admin-form label{
    display:flex;
    flex-direction:column;
    gap:.4rem;
    font-size:.75rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.8;
}

.admin-form input,
.admin-form select,
.admin-form textarea{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.8rem;
    font-family:inherit;
    font-size:.9rem;
}

.admin-form select option{
    background:var(--bg);
    color:var(--text);
}

.checkbox-list{
    display:flex;
    flex-direction:column;
    gap:.5rem;
    border:1px solid var(--border);
    padding:1rem;
    max-height:200px;
    overflow-y:auto;
}

.checkbox-item{
    flex-direction:row !important;
    align-items:center;
    gap:.6rem !important;
    text-transform:none !important;
    font-size:.85rem !important;
    opacity:1 !important;
}

.admin-form__errors{
    background:rgba(176,46,38,.1);
    border:1px solid var(--accent);
    color:var(--accent);
    padding:.8rem 1rem;
    font-size:.8rem;
}

.admin-btn{
    background:var(--accent);
    color:#fff;
    border:none;
    padding:1rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
}

.whitelist-panel{
    margin-top:3rem;
    border-top:1px solid var(--border);
    padding-top:2rem;
}

.whitelist-panel h2{
    font-size:1rem;
    text-transform:uppercase;
    margin-bottom:1rem;
}

.whitelist-row{
    display:grid;
    grid-template-columns:1fr auto auto;
    align-items:center;
    gap:1rem;
    padding:.8rem 0;
    border-bottom:1px solid var(--border);
    font-size:.85rem;
}

.drop-status{
    font-size:.7rem;
    text-transform:uppercase;
    padding:.3rem .8rem;
    border:1px solid var(--border);
}

.drop-status--upcoming{ color:#e0a800; border-color:#e0a800; }
.drop-status--active{ color:#2ecc71; border-color:#2ecc71; }
.drop-status--ended{ color:var(--accent); border-color:var(--accent); }

.whitelist-row__actions{
    display:flex;
    gap:.5rem;
}

.whitelist-row__actions button{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    font-size:.7rem;
    text-transform:uppercase;
    padding:.4rem .8rem;
    cursor:pointer;
}

.whitelist-row__actions button:hover{
    background:var(--accent);
    border-color:var(--accent);
    color:#fff;
}

.admin-panel__empty{
    opacity:.5;
    font-size:.85rem;
}
</style>
@endsection