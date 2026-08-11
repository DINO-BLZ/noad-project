@extends('layouts.app')

@section('content')
<div class="admin-drops">
    <h1>Drops</h1>
    <p>Liste des drops disponibles pour la boutique.</p>

    <div class="admin-drops__list">
        <p>Aucun drop à afficher pour le moment.</p>
    </div>
</div>

<style>
.admin-drops{
    padding:3rem;
}
.admin-drops__list{
    margin-top:2rem;
}
</style>
@endsection
