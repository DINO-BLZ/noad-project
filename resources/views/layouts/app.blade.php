<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Noad — No Advantage</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    @include('layouts.partials.header')

    <main>
        @yield('content')
    </main>

</body>
</html>