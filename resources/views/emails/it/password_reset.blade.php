@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
    # {{ $greeting }}
@else
    @if ($level == 'error')
        # Whoops!
    @else
        # Ciao!
    @endif
@endif

{{-- Intro Lines --}}
Ricevi questa email perché abbiamo ricevuto una richiesta di ripristino della password per il tuo account.

{{-- Action Button --}}
@isset($actionText)
<?php
switch ($level) {
    case 'success':
        $color = 'green';
        break;
    case 'error':
        $color = 'red';
        break;
    default:
        $color = 'blue';
}
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
    {{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
Se non hai richiesto un ripristino della password, non è necessaria alcuna azione aggiuntiva.

{{-- Salutation --}}
@if (! empty($salutation))
    {{ $salutation }}
@else
    Grazie,<br>{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
@component('mail::subcopy')
    Se hai problemi a fare clic su "{{ $actionText }}", copia e incolla l'URL qui sotto nel tuo browser web:: [{{ $actionUrl }}]({{ $actionUrl }})
@endcomponent
@endisset
@endcomponent