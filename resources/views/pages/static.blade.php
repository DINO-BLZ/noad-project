@extends('layouts.app')

@section('content')
    <section style="padding: 4rem 1.5rem;">
        <div style="max-width: 960px; margin: 0 auto; background: #f5f1ea; border: 1px solid rgba(20,20,20,0.1); padding: 3rem;">
            <p style="font-size: 0.72rem; letter-spacing: 0.2em; text-transform: uppercase; margin-bottom: 1rem; color: #7a6b5c;">
                NOAD
            </p>

            <h1 style="font-size: clamp(2rem, 3vw, 3.5rem); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.08em;">
                {{ $title }}
            </h1>

            <p style="font-size: 1rem; line-height: 1.7; margin-bottom: 1.5rem; color: #2e2a26;">
                {{ $subtitle }}
            </p>

            <div style="font-size: 1rem; line-height: 1.8; color: #463f39;">
                {{ $content }}
            </div>
        </div>
    </section>
@endsection
