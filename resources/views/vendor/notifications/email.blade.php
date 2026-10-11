@component('mail::message')
{{-- Greeting (2026-10-12: a normal line instead of a big heading) --}}
@if (! empty($greeting))
<p>{{ $greeting }}</p>
@else
<p>Guten Tag,</p>
@endif

{{--
    Intro / outro lines are printed unescaped: they are our own HTML. Everything
    users typed is escaped by the notifications (App\Support\MailText,
    App\Support\TicketMail) and sent as one HTML block per line, so Markdown
    does not touch it.
--}}
@foreach ($introLines as $line)
{!! $line !!}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{!! $line !!}

@endforeach

{{-- Salutation: "Mit freundlichen Grüßen\nIhre IT" / "…\nIhr Korso-Team" (TicketMail::salutation) --}}
<p>{!! nl2br(e(! empty($salutation) ? $salutation : "Mit freundlichen Grüßen\nIhre IT")) !!}</p>

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
Falls die Schaltfläche „{{ $actionText }}“ nicht funktioniert, kopieren Sie diese Adresse in Ihren Browser: <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
@endslot
@endisset
@endcomponent
