@props(['headerSubtitle' => null, 'title' => null])

<div class="header">
    <h1>{{ $title ?? '👋 Welcome to ' . config('app.name') }}</h1>
    @if($headerSubtitle)
        <p class="info">{{ $headerSubtitle }}</p>
    @endif
</div>

