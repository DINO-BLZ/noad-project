<x-mail::message>
# {{ $whitelist->status === 'approved' ? 'Bonne nouvelle !' : 'Mise à jour de votre demande' }}

Bonjour {{ $whitelist->user->name }},

@if($whitelist->status === 'approved')
Votre demande de whitelist pour le drop **{{ $whitelist->drop->name }}** a été **approuvée**. Vous pouvez maintenant acheter les produits de ce drop.

<x-mail::button :url="route('drops.show', $whitelist->drop->slug)">
Voir le drop
</x-mail::button>
@else
Votre demande de whitelist pour le drop **{{ $whitelist->drop->name }}** a malheureusement été **refusée**.
@endif

Merci,<br>
{{ config('app.name') }}
</x-mail::message>