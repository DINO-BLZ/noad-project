<x-mail::message>
# Réinitialisation de mot de passe

Bonjour {{ $user->name }},

Vous avez demandé la réinitialisation de votre mot de passe sur {{ config('app.name') }}. Cliquez sur le bouton ci-dessous pour en choisir un nouveau.

<x-mail::button :url="$resetUrl">
Réinitialiser mon mot de passe
</x-mail::button>

Ce lien expire dans {{ config('auth.passwords.users.expire', 60) }} minutes.

Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email — votre mot de passe restera inchangé.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>