@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="hero__slogan">
        DEFEND YOUR PRINCIPLE
    </p>

    <a href="{{ route('shop.index') }}" class="hero__cta">
        DÉCOUVRIR
    </a>
</section>

@if($activeDrop)
    <section class="home-drop home-drop--active">
        <p class="home-drop__label">Drop actif</p>
        <h2 class="home-drop__name">{{ $activeDrop->name }}</h2>
        <p class="home-drop__desc">{{ $activeDrop->description }}</p>
        <a href="{{ route('drops.show', $activeDrop->slug) }}" class="home-drop__cta">Voir le drop</a>
    </section>
@elseif($upcomingDrop)
    <section class="home-drop home-drop--upcoming">
        <p class="home-drop__label">Prochain drop</p>
        <h2 class="home-drop__name">{{ $upcomingDrop->name }}</h2>
        <p class="home-drop__countdown" data-target="{{ $upcomingDrop->start_date->toIso8601String() }}">Chargement...</p>
        <a href="{{ route('drops.show', $upcomingDrop->slug) }}" class="home-drop__cta">En savoir plus</a>
    </section>
@endif

@if($newProducts->count() > 0)
    <section class="home-new">
        <h2 class="home-new__title">Nouveautés</h2>
        <div class="home-new__grid">
            @foreach($newProducts as $product)
                <a href="{{ route('products.show', $product->slug) }}" class="home-new__item">
                    <div class="home-new__image">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                    </div>
                    <p class="home-new__name">{{ $product->name }}</p>
                    <p class="home-new__price">{{ number_format($product->price, 0) }} DA</p>
                </a>
            @endforeach
        </div>
    </section>
@endif
@endsection