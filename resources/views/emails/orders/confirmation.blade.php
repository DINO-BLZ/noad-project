<x-mail::message>
# Merci pour votre commande !

Bonjour {{ $order->full_name }},

Votre commande **#{{ $order->id }}** a bien été enregistrée.

<x-mail::table>
| Produit | Taille | Qté | Prix |
|:--------|:------:|:---:|-----:|
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->variant_size }} | {{ $item->quantity }} | {{ number_format($item->price, 0) }} DA |
@endforeach
</x-mail::table>

**Total : {{ number_format($order->total, 0) }} DA**

Livraison à : {{ $order->address }}, {{ $order->wilaya }}
Paiement : {{ $order->payment_method === 'cod' ? 'À la livraison' : 'Carte CIB / Edahabia' }}

<x-mail::button :url="route('checkout.success', $order->id)">
Voir ma commande
</x-mail::button>

Merci de votre confiance,<br>
{{ config('app.name') }}
</x-mail::message>