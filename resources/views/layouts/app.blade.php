<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ html_entity_decode((string) $__env->yieldContent('title', 'Noad — No Advantage'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</title>
    <meta name="description" content="{{ html_entity_decode((string) $__env->yieldContent('meta_description', 'NOAD — vêtements et drops en édition limitée.'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ html_entity_decode((string) $__env->yieldContent('title', 'Noad — No Advantage'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
    <meta property="og:description" content="{{ html_entity_decode((string) $__env->yieldContent('meta_description', 'NOAD — vêtements et drops en édition limitée.'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ html_entity_decode((string) $__env->yieldContent('title', 'Noad — No Advantage'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
    <meta name="twitter:description" content="{{ html_entity_decode((string) $__env->yieldContent('meta_description', 'NOAD — vêtements et drops en édition limitée.'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    @include('layouts.partials.header')

    <main>
        @yield('content')
    </main>

</body>
</html>