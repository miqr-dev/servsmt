{{--
    Plain-text part of every mail (2026-10-12). Overrides the framework's
    text/message.blade.php: German footer, and the HTML of our lines
    (paragraphs, fact tables, quotes) turned into readable text.
--}}
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ config('app.name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {!! \App\Support\MailText::plain((string) $slot) !!}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {!! \App\Support\MailText::plain((string) $subcopy) !!}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            Diese E-Mail wurde automatisch von Servsmt erstellt. Bitte nicht antworten – Rückfragen bitte als Kommentar im Ticket.
            © {{ date('Y') }} MIQR
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
