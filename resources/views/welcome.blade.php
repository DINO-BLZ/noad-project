<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Noad — No Advantage</title>
</head>

<body>

    @include('layouts.partials.header')

    <section class="hero">
        <p class="hero__slogan">
            DEFEND YOUR PRINCIPLE
        </p>

        <a href="#" class="hero__cta">
            DÉCOUVRIR
        </a>
    </section>

    <style>
        .hero{
            min-height:70vh;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:2rem;
            text-align:center;
        }

        .hero__slogan{
            font-family:'Archivo',sans-serif;
            font-weight:900;
            letter-spacing:.05em;
            font-size:clamp(1.5rem,5vw,3rem);
            color:var(--text);
            margin:0;
        }

        .hero__cta{
            font-family:'Archivo',sans-serif;
            font-weight:700;
            font-size:.85rem;
            letter-spacing:.1em;
            color:var(--bg);
            background:var(--text);
            padding:.9rem 2.2rem;
            text-decoration:none;
            transition:.2s;
        }

        .hero__cta:hover{
            background:var(--accent);
            color:var(--text);
        }
    </style>

</body>
</html>