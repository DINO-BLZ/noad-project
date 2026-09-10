<x-mail::message>
# Le drop est ouvert

Bonjour {{ $whitelist->user->name }},

Le drop **{{ $whitelist->drop->name }}** vient de commencer. Vous êtes whitelisté : vous pouvez maintenant acheter les produits de ce drop.

<x-mail::button :url="route('drops.show', $whitelist->drop->slug)">
Voir le drop
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
