@component('emails.template.html.message')
    <h1>MemberShip Attivata</h1>
    <p>La MemberShip dell'utente  {{ $name }} è stata attivata</p>
    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Thanks, {{ config('app.name') }}
    @endif
@endcomponent
