@component('emails.template.html.message')
    <h1>Activate MemberShip</h1>
    <p>The user's {{ $name }} Membership was activated</p>
    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Thanks, {{ config('app.name') }}
    @endif
@endcomponent
