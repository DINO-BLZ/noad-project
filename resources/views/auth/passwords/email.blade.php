@extends('layouts.app')

@section('content')
<div class="auth">
    <div class="auth__card">
        <h1 class="auth__title">Mot de passe oublié</h1>

        <p class="auth__hint">
            Indiquez votre adresse email, nous vous enverrons un lien pour choisir un nouveau mot de passe.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="auth__form">
            @csrf

            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>

            @if (session('success'))
                <div class="auth__success">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="auth__errors">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="auth__submit">Envoyer le lien</button>
        </form>

        <p class="auth__switch">
            <a href="{{ route('login') }}">Retour à la connexion</a>
        </p>
    </div>
</div>

<style>
.auth{
    min-height:70vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:3rem 1.5rem;
}

.auth__card{
    width:100%;
    max-width:420px;
}

.auth__title{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.03em;
    margin-bottom:.8rem;
    text-align:center;
}

.auth__hint{
    font-size:.85rem;
    opacity:.7;
    text-align:center;
    margin-bottom:1.5rem;
}

.auth__form{
    display:flex;
    flex-direction:column;
    gap:1.2rem;
}

.auth__form label{
    display:flex;
    flex-direction:column;
    gap:.4rem;
    font-size:.75rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.08em;
    opacity:.8;
}

.auth__form input{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.9rem;
    font-size:.9rem;
    font-family:inherit;
}

.auth__form input:focus{
    outline:none;
    border-color:var(--accent);
}

.auth__errors{
    background:rgba(176,46,38,.1);
    border:1px solid var(--accent);
    color:var(--accent);
    padding:.8rem 1rem;
    font-size:.8rem;
}

.auth__errors p{
    margin:.2rem 0;
}

.auth__success{
    background:rgba(46,176,80,.1);
    border:1px solid #2eb050;
    color:#2eb050;
    padding:.8rem 1rem;
    font-size:.8rem;
}

.auth__success p{
    margin:0;
}

.auth__submit{
    background:var(--accent);
    color:#fff;
    border:none;
    padding:1rem;
    font-weight:700;
    font-size:.85rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
    transition:.2s;
}

.auth__submit:hover{
    opacity:.9;
}

.auth__switch{
    text-align:center;
    margin-top:1.5rem;
    font-size:.85rem;
    opacity:.7;
}

.auth__switch a{
    color:var(--accent);
    text-decoration:none;
    font-weight:600;
}
</style>
@endsection
