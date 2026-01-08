@props([
    'action' => '',
    'method' => 'DELETE',
    'confirm' => 'Are you sure you want to delete this item?',
    'icon' => 'trash',
])

<form action="{{ $action }}" method="POST" class="inline-form" onsubmit="return confirm('{{ $confirm }}')">
    @csrf
    @method($method)
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-danger']) }}>
        <i class="fas fa-{{ $icon }}"></i>
        {{ $slot }}
    </button>
</form>

<style>
    .inline-form {
        display: inline;
    }
</style>
