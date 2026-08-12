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
@endsection