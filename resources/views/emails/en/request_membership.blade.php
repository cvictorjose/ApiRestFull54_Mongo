@component('emails.template.html.message')
    <h1>New Request MemberShip</h1>
    <p>The user {{ $name }} is requesting a new Membership</p>
    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Thanks, {{ config('app.name') }}
    @endif
@endcomponent
