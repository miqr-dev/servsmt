<!DOCTYPE html>
<html>
<head>
    <title>{{ ucfirst($city) }} Offene Tickets</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .container {
            padding: 20px;
        }
        .card {
            position: relative; /* Needed for the pseudo-element */
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            page-break-inside: avoid; /* Prevent card from splitting across pages */
            overflow: hidden; /* Prevent pseudo-element from overflowing */
            font-size: 12px; /* Smaller font size */
            line-height: 1.2; /* Reduced space between lines */
        }
        .card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 25%;
            bottom: 0;
            width: 1px;
            background: #ddd;
        }
        .card h3 {
            margin: 0 0 10px;
            font-size: 16px; /* Adjusted font size for the heading */
            display: inline-block;
        }
        .card .notes {
            float: right;
            font-size: 12px; /* Adjusted font size for the notes */
            font-weight: bold;
            margin-top: 4px;
            margin-right: 30px;
        }
        .card p {
            margin: 5px 0;
            max-width: 75%; /* Limit text length to 3/4 of the card */
            word-wrap: break-word;
        }
        .card strong {
            display: inline-block;
            width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ ucfirst($city) }} Offene Tickets</h1>
        @foreach($openHandwerks as $handwerk)
            <div class="card">
                @if($handwerk->problem_type)
                    <h3>{{ $handwerk->problem_type }}</h3>
                    <span class="notes">Notizen</span>
                @endif
                @if($handwerk->submitter_name)
                    <p><strong>Ersteller:</strong> {{ $handwerk->submitter_name }}</p>
                @endif
                @if($handwerk->submitter_standort)
                    <p><strong>Standort:</strong> {{ $handwerk->submitter_standort }}</p>
                @endif
                @if($handwerk->submitter_adresse)
                    <p><strong>Adresse:</strong> {{ $handwerk->submitter_adresse }}</p>
                @endif              
                @if($handwerk->tel_number)
                    <p><strong>Telefonnummer:</strong> {{ $handwerk->tel_number }}</p>
                @endif
                @if($handwerk->assignedTo)
                    <p><strong>Zugewiesen an:</strong> {{ $handwerk->assignedTo }}</p>
                @endif
                @if($handwerk->done_by)
                    <p><strong>Erledigt von:</strong> {{ $handwerk->done_by }}</p>
                @endif
                @if($handwerk->location)
                    <p><strong>Standort:</strong> {{ $handwerk->location->address }}</p>
                @endif
                @if($handwerk->room)
                    <p><strong>Raum:</strong> {{ $handwerk->room->rname }}</p>
                @endif

                @foreach(['schiebetafel', 'whiteboard', 'kreidetafel', 'schreibtisch_TN_70x70', 'schreibtisch_TN_80x80', 'schreibtisch_TN_80x160', 'schreibtisch_DOZ_80x140', 'schreibtisch_DOZ_80x160', 'schreibtisch_DOZ_80x180', 'schreibtisch_MA_80x140', 'schreibtisch_MA_80x160', 'schreibtisch_MA_80x180', 'stehtisch', 'gesprächstisch_rund', 'konferenztisch', 'couchtisch', 'beistelltisch', 'schreibtischstuhl', 'bürostuhl', 'stapelstühl', 'rollcontainer', 'standcontainer', 'hochschrank', 'ordnerhöhen_2', 'ordnerhöhen_3', 'hängeschrank', 'lamellenvorhang', 'rollo', 'pinnwand', 'bilder', 'handtuchspender', 'toilettenpapierhalter', 'desinfektionsmittelspender', 'barzeile', 'bar_Hochstühle', 'küchenzeile', 'neustandort_room', 'kühlschrank', 'ventilator', 'geschirrspüler', 'kaffeemaschine', 'notizen', 'subject', 'custom_room'] as $field)
                    @if($handwerk->$field)
                        <p><strong>{{ ucfirst($field) }}:</strong> 
                            @if($handwerk->$field === 'on' && $handwerk->{$field . '_qty'})
                                {{ $handwerk->{$field . '_qty'} }}
                            @elseif($handwerk->$field === 'on')
                                {{ 'Ja' }}
                            @else
                                {!! $handwerk->$field !!}
                            @endif
                        </p>
                    @endif
                @endforeach

                <p><strong>Erstellt am:</strong> {{ $handwerk->created_at->format('d.m.Y H:i') }}</p>
                
                @if($handwerk->admin_notes)
                    <p><strong>Beschreibung:</strong> {!! nl2br(e($handwerk->admin_notes)) !!}</p>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
