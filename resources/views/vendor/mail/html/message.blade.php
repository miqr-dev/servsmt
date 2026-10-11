@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer (2026-10-12: German, the same for every mail, no APP_NAME) --}}
@slot('footer')
@component('mail::footer')
Diese E-Mail wurde automatisch von Servsmt erstellt. Bitte nicht antworten – Rückfragen bitte als Kommentar im Ticket.<br>
© {{ date('Y') }} MIQR
@endcomponent
@endslot
@endcomponent
