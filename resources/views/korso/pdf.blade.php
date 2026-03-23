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

    @php
        $cleanNotes = $korso->notizen;
        if ($korso->is_chatgpt_project && is_string($cleanNotes) && strpos($cleanNotes, '<h5>ChatGPT-Projektvorschläge</h5>') !== false) {
            $cleanNotes = strpos($cleanNotes, '<hr>') !== false ? strstr($cleanNotes, '<hr>', true) : null;
        }
    @endphp
    @if(!empty(trim(strip_tags($cleanNotes ?? ''))))
    <div class="section">
        <h2>Beschreibung</h2>
        <p>{!! $cleanNotes !!}</p>
    </div>
    @endif


    @if($korso->is_chatgpt_project)
    <div class="section">
        <h2>ChatGPT-Projektvorschläge</h2>
        <table class="info-table">
            <tr><th>Projektname</th><td>{{ $korso->chatgpt_project_name ?: '—' }}</td></tr>
            <tr><th>Einführungsgrund</th><td>{!! nl2br(e($korso->chatgpt_introduction_reason ?: '—')) !!}</td></tr>
            <tr><th>Ziele</th><td>{!! nl2br(e($korso->chatgpt_goal ?: '—')) !!}</td></tr>
            <tr><th>Prozessschritte</th><td>{!! nl2br(e($korso->chatgpt_process_steps ?: '—')) !!}</td></tr>
            <tr><th>Bestehender Prozess</th><td>{{ is_null($korso->chatgpt_has_existing_process) ? '—' : ($korso->chatgpt_has_existing_process ? 'Ja' : 'Nein') }}</td></tr>
            <tr><th>Output-Beispiele vorhanden</th><td>{{ is_null($korso->chatgpt_has_output_examples) ? '—' : ($korso->chatgpt_has_output_examples ? 'Ja' : 'Nein') }}</td></tr>
            <tr><th>Knowledge Base vorhanden</th><td>{{ is_null($korso->chatgpt_has_knowledge_base) ? '—' : ($korso->chatgpt_has_knowledge_base ? 'Ja' : 'Nein') }}</td></tr>
            <tr><th>Perfekter Output</th><td>{!! nl2br(e($korso->chatgpt_output_examples ?: '—')) !!}</td></tr>
            <tr><th>Vorhandenes Wissen</th><td>{!! nl2br(e($korso->chatgpt_knowledge_base ?: '—')) !!}</td></tr>
            <tr><th>Sonstige Anforderungen</th><td>{!! nl2br(e($korso->chatgpt_additional_requirements ?: '—')) !!}</td></tr>
        </table>
    </div>
    @endif

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
                @if($attachment->context)
                    <p><strong>{{ $attachment->context }}</strong></p>
                @endif
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
