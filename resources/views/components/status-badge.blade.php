@props(['status'])

@php
$labels = [
    'pending' => 'En attente',
    'approved' => "Accept\u{00E9}e",
    'rejected' => "Refus\u{00E9}e",
    'upcoming' => "\u{00C0} venir",
    'active' => 'En cours',
    'ended' => "Termin\u{00E9}",
];

$cssClass = match ($status) {
    'approved', 'active' => 'active',
    'rejected', 'ended' => 'ended',
    default => 'upcoming',
};

$label = $labels[$status] ?? ucfirst($status);
@endphp

<span class="drop-status drop-status--{{ $cssClass }}">{{ $label }}</span>