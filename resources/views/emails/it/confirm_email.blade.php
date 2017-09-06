@component('emails.template.html.message')
    <h1>Ciao {{ $name }}</h1>
    <p>Per cortesia conferma la tua E-mail, cliccando il seguente pulsante.</p>

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
        Se hai problemi a fare clic sul pulsante  "{{ $actionText }}", copia e incolla l'URL qui sotto nel tuo browser web: [{{ $actionUrl }}]
    @endcomponent
    @endisset
@endcomponent




