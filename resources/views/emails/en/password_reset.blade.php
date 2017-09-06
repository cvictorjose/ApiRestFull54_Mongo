@component('emails.template.html.message')
    <h1>Hi {{ $name }}</h1>
    <p>You are receiving this email because we received a password reset request for your account.</p>

    @component('emails.template.html.button', ['url' => $actionUrl, 'color' => 'blue'])
        {{ $actionText }}
    @endcomponent

    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Thanks, {{ config('app.name') }}
    @endif


    @isset($actionText)
    @component('emails.template.html.subcopy')
        If you’re having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser: [{{ $actionUrl }}]
    @endcomponent
    @endisset
@endcomponent
