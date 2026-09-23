@props([
    'hover' => false,
    'glass' => false,
    'padding' => 'p-6',
])

@php
    $baseClasses = 'rounded-2xl border transition-all duration-200';
    $surfaceClasses = $glass 
        ? 'glass shadow-lg' 
        : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800/80 shadow-soft';
    $hoverClasses = $hover 
        ? 'hover:shadow-soft-lg hover:-translate-y-0.5 hover:border-slate-300 dark:hover:border-slate-700 cursor-pointer' 
        : '';
@endphp

<div {{ $attributes->merge(['class' => "$baseClasses $surfaceClasses $hoverClasses $padding"]) }}>
    {{ $slot }}
</div>
