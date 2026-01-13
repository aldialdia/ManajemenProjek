@props([
    'type' => 'button',
    'href' => null,
    'icon' => null,
])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-primary']) }}>
        @if($icon)
            <i class="fas fa-{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-primary']) }}>
        @if($icon)
            <i class="fas fa-{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif
