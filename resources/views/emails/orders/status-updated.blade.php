<x-mail::message>
# Mise à jour de votre commande

Bonjour {{ $order->full_name }},

Le statut de votre commande **{{ $order->formatted_reference }}** est maintenant : **{{ $order->status->label() }}**.

<x-mail::button :url="route('checkout.success', $order->id)">
Voir ma commande
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
