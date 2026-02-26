@props(['onclick' => null, 'type' => 'button'])

<button 
    class="button" 
    @if($onclick) onclick="{{ $onclick }}" @endif
    type="{{ $type }}"
    {{ $attributes }}
>
    {{ $slot }}
</button>

