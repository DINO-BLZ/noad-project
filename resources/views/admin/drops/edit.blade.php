@extends('layouts.app')

@section('content')
<div class="admin-drop-edit">
    <h1>Modifier le drop</h1>
    <p>Édition du drop #{{ $id }}.</p>

    <form>
        <label>Nom du drop
            <input type="text" name="name" value="" disabled>
        </label>
        <label>Date de lancement
            <input type="text" name="launch_date" value="" disabled>
        </label>
        <button type="submit" disabled>Enregistrement non activé</button>
    </form>
</div>

<style>
.admin-drop-edit{
    padding:3rem;
    max-width:700px;
}
.admin-drop-edit form{
    display:grid;
    gap:1rem;
    margin-top:2rem;
}
.admin-drop-edit input{
    width:100%;
    padding:.8rem;
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
}
.admin-drop-edit button{
    width:fit-content;
    padding:.9rem 1.5rem;
    background:var(--accent);
    border:none;
    color:#fff;
    cursor:not-allowed;
}
</style>
@endsection
