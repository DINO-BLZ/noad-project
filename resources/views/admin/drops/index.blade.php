@extends('layouts.admin')

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
               <x-status-badge :status="$drop->status" />
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
@endsection