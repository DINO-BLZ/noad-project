<x-mail::message>
# Digest quotidien — {{ $date->format('d/m/Y') }}

## Ventes
- Commandes confirmées : {{ $sales['confirmed_orders'] }}
- Articles vendus : {{ $sales['items_sold'] }}
- Chiffre d'affaires : {{ number_format($sales['revenue'], 2, ',', ' ') }} DA

## Top produits
@if(!empty($sales['top_products']))
@foreach($sales['top_products'] as $product)
- {{ $product['name'] }} — {{ $product['quantity'] }} vendu(s)
@endforeach
@endif

## Commandes
- En attente : {{ $orders['pending'] }}
- Annulées : {{ $orders['cancelled'] }}

## Whitelist
- Nouvelles : {{ $whitelist['new'] }}
- Approuvées : {{ $whitelist['approved'] }}
- Refusées : {{ $whitelist['rejected'] }}

## Stock critique
- {{ $stock['critical_count'] }} variante(s) concernée(s)
@if(!empty($stock['variants']))
@foreach($stock['variants'] as $variant)
- {{ $variant['product_name'] }} ({{ $variant['size'] }}/{{ $variant['color'] }}) : {{ $variant['stock'] }} restant(s)
@endforeach
@endif

Merci,<br>
{{ config('app.name') }}
</x-mail::message>