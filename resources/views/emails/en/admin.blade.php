@component('emails.template.html.message')

    @if ($level==1)
        <h1>Request Change WebStore</h1>
    @endif


    <p>{{ $body }}</p>

    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Thanks, {{ config('app.name') }}
    @endif
@endcomponent
