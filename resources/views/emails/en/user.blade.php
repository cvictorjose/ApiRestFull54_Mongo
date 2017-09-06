@component('emails.template.html.message')

    @if ($level==5)
        <h1>Confirmed your new Web Store</h1>
    @endif


    <p>{{ $body }}</p>

    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Thanks, {{ config('app.name') }}
    @endif
@endcomponent
