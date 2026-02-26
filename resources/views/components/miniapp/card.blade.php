@props(['title' => null])

<div class="card">
    @if($title)
        <h2 style="margin-top: 0;">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>

