@extends('layouts.app')

@section('title', $title.' — NOAD')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($subtitle), 160))

@section('content')
    <section class="static-page">
        <div class="static-page__content">
            <p class="static-page__eyebrow">
                NOAD
            </p>

            <h1 class="static-page__title">
                {{ $title }}
            </h1>

            <p class="static-page__subtitle">
                {{ $subtitle }}
            </p>

            <div class="static-page__body">
                {{ $content }}
            </div>
        </div>
    </section>
@endsection
