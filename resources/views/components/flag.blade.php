@props([
    'country' => 'uz',
    'class' => 'w-4.5 h-3 rounded-[2px] shadow-xs shrink-0 inline-block align-middle border border-white/10'
])

@php
    $c = strtolower(trim($country));
    if ($c === 'en') $c = 'gb';
@endphp

@if($c === 'uz')
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg" aria-label="O'zbekiston bayrog'i">
        <path fill="#0099b5" d="M0 0h640v160H0z"/>
        <path fill="#ce1126" d="M0 155h640v10H0z"/>
        <path fill="#fff" d="M0 160h640v160H0z"/>
        <path fill="#ce1126" d="M0 315h640v10H0z"/>
        <path fill="#1eb53a" d="M0 320h640v160H0z"/>
        <circle cx="70" cy="80" r="34" fill="#fff"/>
        <circle cx="82" cy="80" r="34" fill="#0099b5"/>
        <g fill="#fff">
            <circle cx="130" cy="45" r="6"/><circle cx="155" cy="45" r="6"/><circle cx="180" cy="45" r="6"/>
            <circle cx="130" cy="68" r="6"/><circle cx="155" cy="68" r="6"/><circle cx="180" cy="68" r="6"/>
            <circle cx="130" cy="92" r="6"/><circle cx="155" cy="92" r="6"/><circle cx="180" cy="92" r="6"/>
            <circle cx="130" cy="115" r="6"/><circle cx="155" cy="115" r="6"/><circle cx="180" cy="115" r="6"/>
        </g>
    </svg>
@elseif($c === 'ru')
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg" aria-label="Rossiya bayrog'i">
        <path fill="#fff" d="M0 0h640v160H0z"/>
        <path fill="#0039a6" d="M0 160h640v160H0z"/>
        <path fill="#d52b1e" d="M0 320h640v160H0z"/>
    </svg>
@elseif($c === 'gb')
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg" aria-label="Buyuk Britaniya bayrog'i">
        <path fill="#012169" d="M0 0h640v480H0z"/>
        <path fill="#fff" d="m75 0 245 180L565 0h75v60L440 240l200 180v60h-75L320 300 75 480H0v-60l200-180L0 60V0z"/>
        <path fill="#c8102e" d="m425 285 215 155v40L395 285zm-210-90L0 40V0l245 155zm180-45L640 0v10L440 150zM0 435l190-145 35 25L40 480H0z"/>
        <path fill="#fff" d="M260 0h120v480H260zM0 180h640v120H0z"/>
        <path fill="#c8102e" d="M285 0h70v480h-70zM0 205h640v70H0z"/>
    </svg>
@else
    <span class="text-xs leading-none">🌐</span>
@endif
