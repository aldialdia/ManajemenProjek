@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
])

<div class="form-group">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    @if($type === 'textarea')
        <textarea 
            name="{{ $name }}" 
            id="{{ $name }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'form-control' . ($error ? ' is-invalid' : '')]) }}
            rows="4"
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select 
            name="{{ $name }}" 
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'form-control' . ($error ? ' is-invalid' : '')]) }}
        >
            {{ $slot }}
        </select>
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'form-control' . ($error ? ' is-invalid' : '')]) }}
        >
    @endif

    @if($error)
        <span class="error-message">{{ $error }}</span>
    @endif

    @error($name)
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<style>
    .is-invalid {
        border-color: #ef4444 !important;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.375rem;
        display: block;
    }
</style>
