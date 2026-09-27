@props(['paginator'])

@if (isset($paginator) && $paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'pt-6']) }}>
        {{ $paginator->links('vendor.pagination.taste-livewire') }}
    </div>
@endif
