@component('emails.template.html.message')
    <h1>Nuova richiesta per la MemberShip</h1>
    <p>L'utente {{ $name }} sta richiedendo una nuova Membership</p>
    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Grazie, {{ config('app.name') }}
    @endif
@endcomponent




