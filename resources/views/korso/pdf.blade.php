<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Korso Ticket #{{ $korso->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0; padding: 0; }
        .section { margin-bottom: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td, .info-table th { padding: 5px; border: 1px solid #ddd; }
        .attachments img { max-width: 100px; margin: 5px; }
        ul { list-style-type: disc; padding-left: 20px; }
        li { margin-bottom: 5px; }
        /* Resize any images within comments */
        .comment-content img {
            max-width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Korso Ticket</h1>
        <p>Ticket ID: {{ $korso->id }}</p>
    </div>

    <div class="section">
        <h2>Ticket Informationen</h2>
        <table class="info-table">
            <tr>
                <th>Ersteller</th>
                <td>{{ $korso->subUser->name ?? 'Unknown' }}</td>
            </tr>
            <tr>
                <th>Erstellt am</th>
                <td>{{ $korso->created_at->format('d M Y') }}</td>
            </tr>
            <tr>
                <th>Telefon</th>
                <td>{{ $korso->tel_number }}</td>
            </tr>
            <tr>
                <th>Adresse</th>
                <td>{{ $korso->submitter_adresse }}</td>
            </tr>
            <tr>
                <th>Priorität</th>
                <td>
                    @if($korso->priority == 3)
                        Hoch
                    @elseif($korso->priority == 2)
                        Normal
                    @elseif($korso->priority == 1)
                        Niedrig
                    @else
                        Unbekannt
                    @endif
                </td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $korso->ticket_status->name ?? 'Unbekannt' }}</td>
            </tr>
            <tr>
                <th>Zugewiesen an</th>
                <td>
                    @if($korso->assignedUser)
                        {{ $korso->assignedUser->name }}
                    @else
                        Nicht zugewiesen
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Beschreibung</h2>
        <p>{!! $korso->notizen !!}</p>
    </div>

    @if($korso->massnahme)
    <div class="section">
        <h2>Maßnahme</h2>
        <p>{{ $korso->massnahme->name }}</p>
    </div>
    @endif

    <div class="section attachments">
        <h2>Anhäng</h2>
        @if($korso->korsoAttachments->count() > 0)
            @foreach($korso->korsoAttachments as $attachment)
                @if(str_contains($attachment->file_type, 'image'))
                    <img src="{{ public_path('storage/' . $attachment->file_path) }}" alt="Attachment">
                @else
                    <p>{{ $attachment->file_type }}: {{ $attachment->file_path }}</p>
                @endif
            @endforeach
        @else
            <p>Keine Anhänge vorhanden.</p>
        @endif
    </div>

    <div class="section">
        <h2>Interne Kommentare</h2>
        @if($korso->internalComments->count() > 0)
            <ul>
                @foreach($korso->internalComments as $comment)
                    <li>
                        <strong>{{ @$comment->user->name }}</strong>: {{ $comment->comment }}
                        <small>({{ @$comment->created_at->format('d M Y H:i') }})</small>
                    </li>
                @endforeach
            </ul>
        @else
            <p>Keine internen Kommentare vorhanden.</p>
        @endif
    </div>

    <div class="section">
        <h2>Öffentliche Kommentare</h2>
        @if($korso->comments->count() > 0)
            <ul>
                @foreach($korso->comments as $comment)
                    <li>
                        <!-- Using the "commenter" relationship (Laravelista Comments v3.6) -->
                        <strong>{{ $comment->commenter->name ?? 'Anonymous' }}</strong>:
                        <div class="comment-content">
                            {!! html_entity_decode($comment->comment) !!}
                        </div>
                        <small>({{ $comment->created_at->format('d M Y H:i') }})</small>
                    </li>
                @endforeach
            </ul>
        @else
            <p>Keine öffentlichen Kommentare vorhanden.</p>
        @endif
    </div>
    
    <!-- Additional sections (Bestellte Artikel, Bestellung Flyer, etc.) can be added as needed -->
    
</body>
</html>
