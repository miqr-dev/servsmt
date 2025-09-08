<!DOCTYPE html>
<html>

<head>
  <title>City Tickets PDF</title>
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
      position: relative;
      /* Needed for the pseudo-element */
      border: 1px solid #ddd;
      border-radius: 5px;
      margin-bottom: 20px;
      padding: 15px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
      page-break-inside: avoid;
      /* Prevent card from splitting across pages */
      overflow: hidden;
      /* Prevent pseudo-element from overflowing */
      font-size: 12px;
      /* Smaller font size */
      line-height: 1.2;
      /* Reduced space between lines */
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
      font-size: 16px;
      /* Adjusted font size for the heading */
      display: inline-block;
    }

    .card .notes {
      float: right;
      font-size: 12px;
      /* Adjusted font size for the notes */
      font-weight: bold;
      margin-top: 4px;
      margin-right: 30px;
    }

    .card p {
      margin: 5px 0;
      max-width: 75%;
      /* Limit text length to 3/4 of the card */
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
    <h1>{{ $city->pnname }} Offene Tickets</h1>
    @foreach($openTickets as $ticket)
    <div class="card">
      <h3>{{ $ticket->problem_type }}</h3>
      <span class="notes">Notizen</span>

      <p><strong>Ersteller:</strong> {{ $ticket->subUser->username }}</p>
      <p><strong>Standort:</strong> {{ $ticket->subUser->ort }}</p>
      <p><strong>Telefonnummer:</strong> {{ $ticket->tel_number }}</p>

      @if($ticket->gname_id && $ticket->invitem)
      <p><strong>Geräte</strong> {{ $ticket->invitem->gname ?? 'N/A' }}</p>

      @if($ticket->invitem->invroom && $ticket->invitem->invroom->location)
      <p><strong>Geräte Adresse:</strong> {{ $ticket->invitem->invroom->location->address ?? 'N/A' }}</p>
      <p><strong>Geräte Raum:</strong> {{ $ticket->invitem->invroom->rname ?? 'N/A' }}
        <i class="fas fa-grip-lines-vertical"></i> {{ $ticket->invitem->invroom->altrname ?? 'N/A' }}
      </p>
      @else
      <p><strong>Geräte Adresse:</strong> N/A</p>
      <p><strong>Geräte Raum:</strong> N/A</p>
      @endif
      @endif
      @if($ticket->searchsoftware)
      <p><strong>Search Software:</strong> {{ $ticket->searchsoftware }}</p>
      @endif
      @if($ticket->software_name)
      <p><strong>Software Name:</strong> {{ $ticket->software_name }}</p>
      @endif
      @if($ticket->software_reason)
      <p><strong>Software Reason:</strong> {{ $ticket->software_reason }}</p>
      @endif
      @if($ticket->pc_laptop_others)
      <p><strong>PC/Laptop/Other:</strong> {{ $ticket->pc_laptop_others }}</p>
      @endif
      @if($ticket->keyboard)
      <p><strong>Keyboard:</strong> {{ $ticket->keyboard }}</p>
      @endif
      @if($ticket->mouse)
      <p><strong>Mouse:</strong> {{ $ticket->mouse }}</p>
      @endif
      @if($ticket->speaker)
      <p><strong>Speaker:</strong> {{ $ticket->speaker }}</p>
      @endif
      @if($ticket->headset)
      <p><strong>Headset:</strong> {{ $ticket->headset }}</p>
      @endif
      @if($ticket->webcam)
      <p><strong>Webcam:</strong> {{ $ticket->webcam }}</p>
      @endif
      @if($ticket->monitor)
      <p><strong>Monitor:</strong> {{ $ticket->monitor }}</p>
      @endif
      @if($ticket->other)
      <p><strong>Other:</strong> {{ $ticket->other }}</p>
      @endif

      @if($ticket->geht_nicht_an)
      <p><strong style="color: red;">Geht nicht an</strong></p>
      @endif

      @if($ticket->blue)
      <p><strong style="color: red;">Geht an / Blue Screen</strong></p>
      @endif

      @if($ticket->black)
      <p><strong style="color: red;">Geht an / Black Screen</strong></p>
      @endif

      @if($ticket->slow_computer)
      <p><strong style="color: red;">Sehr Langsam</strong></p>
      @endif

      @if($ticket->web_cam_problem)
      <p><strong style="color: red;">Webcam funktioniert nicht</strong></p>
      @endif

      @if($ticket->head_set_problem)
      <p><strong style="color: red;">Headset funktioniert nicht</strong></p>
      @endif

      @if($ticket->lautsprecher_mal)
      <p><strong style="color: red;">Lautsprecher funktioniert nicht</strong></p>
      @endif

      @if($ticket->keyboard_malfunction)
      <p><strong style="color: red;">Tastatur funktioniert nicht</strong></p>
      @endif

      @if($ticket->mouse_mal)
      <p><strong style="color: red;">Maus funktioniert nicht</strong></p>
      @endif

      @if($ticket->slow_network)
      <p><strong style="color: red;">Netzwerkzugriff langsam</strong></p>
      @endif

      @if($ticket->no_network_drive)
      <p><strong style="color: red;">Keine Netzlaufwerke</strong></p>
      @endif

      @if($ticket->laud_fan)
      <p><strong style="color: red;">Lautes Lüftergeräusch</strong></p>
      @endif

      @if($ticket->other)
      <p><strong style="color: red;">Sonstiges</strong></p>
      @endif


      @if($ticket->scanner_wrong_folder)
      <p><strong>Scanner Wrong Folder:</strong> {{ $ticket->scanner_wrong_folder }}</p>
      @endif
      @if($ticket->scanner_not_working)
      <p><strong>Scanner Not Working:</strong> {{ $ticket->scanner_not_working }}</p>
      @endif
      @if($ticket->scanner_myname_list)
      <p><strong>Scanner My Name List:</strong> {{ $ticket->scanner_myname_list }}</p>
      @endif
      @if($ticket->location_id)
      <p><strong>Location ID:</strong> {{ $ticket->location->address }}</p>
      @endif
      @if($ticket->room_id)
      <p><strong>Room ID:</strong> {{ $ticket->room->rname }} || {{ $ticket->room->altrname }}</p>
      @endif
      @if($ticket->printer_name)
      <p><strong>Printer Name:</strong> {{ $ticket->printer_name }}</p>
      @endif
      @if($ticket->gart_id)
      <p><strong>Gart ID:</strong> {{ $ticket->gart->name }}</p>
      @endif
      @if($ticket->replication_id)
      <p><strong>Replication ID:</strong> {{ $ticket->replication_id }}</p>
      @endif
      @if($ticket->position_employee)
      <p><strong>Position Employee:</strong> {{ $ticket->position_employee }}</p>
      @endif
      @if($ticket->abteilung_employee)
      <p><strong>Abteilung Employee:</strong> {{ $ticket->abteilung_employee }}</p>
      @endif
      @if($ticket->telephone_employee)
      <p><strong>Telephone Employee:</strong> {{ $ticket->telephone_employee }}</p>
      @endif
      @if($ticket->outlook)
      <p><strong>Outlook:</strong> {{ $ticket->outlook }}</p>
      @endif
      @if($ticket->isplus)
      <p><strong>Is Plus:</strong> {{ $ticket->isplus }}</p>
      @endif
      @if($ticket->employee_lastname)
      <p><strong>Employee Last Name:</strong> {{ $ticket->employee_lastname }}</p>
      @endif
      @if($ticket->employee_firstname)
      <p><strong>Employee First Name:</strong> {{ $ticket->employee_firstname }}</p>
      @endif
      @if($ticket->employee_required_at)
      <p><strong>Employee Required At:</strong> {{ $ticket->employee_required_at }}</p>
      @endif
      @if($ticket->email_erfurt)
      <p><strong>Email Erfurt:</strong> {{ $ticket->email_erfurt }}</p>
      @endif
      @if($ticket->email_berlin)
      <p><strong>Email Berlin:</strong> {{ $ticket->email_berlin }}</p>
      @endif
      @if($ticket->email_suhl)
      <p><strong>Email Suhl:</strong> {{ $ticket->email_suhl }}</p>
      @endif
      @if($ticket->email_dresden)
      <p><strong>Email Dresden:</strong> {{ $ticket->email_dresden }}</p>
      @endif
      @if($ticket->email_leipzig)
      <p><strong>Email Leipzig:</strong> {{ $ticket->email_leipzig }}</p>
      @endif
      @if($ticket->email_chemnitz)
      <p><strong>Email Chemnitz:</strong> {{ $ticket->email_chemnitz }}</p>
      @endif
      @if($ticket->email_lorenz)
      <p><strong>Email Lorenz:</strong> {{ $ticket->email_lorenz }}</p>
      @endif
      @if($ticket->email_lasch)
      <p><strong>Email Lasch:</strong> {{ $ticket->email_lasch }}</p>
      @endif
      @if($ticket->email_custom)
      <p><strong>Email Custom:</strong> {{ $ticket->email_custom }}</p>
      @endif
      @if($ticket->password_name)
      <p><strong>Password Name:</strong> {{ $ticket->password_name }}</p>
      @endif
      @if($ticket->forgotten)
      <p><strong>Forgotten:</strong> {{ $ticket->forgotten }}</p>
      @endif
      @if($ticket->inaktiv)
      <p><strong>Inaktiv:</strong> {{ $ticket->inaktiv }}</p>
      @endif
      @if($ticket->expiring_date)
      <p><strong>Expiring Date:</strong> {{ $ticket->expiring_date }}</p>
      @endif
      @if($ticket->abgelaufen)
      <p><strong>Abgelaufen:</strong> {{ $ticket->abgelaufen }}</p>
      @endif
      @if($ticket->user_oldname)
      <p><strong>Old Name:</strong> {{ $ticket->user_oldname }}</p>
      @endif
      @if($ticket->user_newname)
      <p><strong>New Name:</strong> {{ $ticket->user_newname }}</p>
      @endif
      @if($ticket->user_other_username)
      <p><strong>Other Username:</strong> {{ $ticket->user_other_username }}</p>
      @endif
      @if($ticket->tel_target_place)
      <p><strong>Tel Target Place:</strong> {{ $ticket->tel_target_place }}</p>
      @endif
      @if($ticket->tel_target_room)
      <p><strong>Tel Target Room:</strong> {{ $ticket->tel_target_room }}</p>
      @endif
      @if($ticket->pro_target_place)
      <p><strong>Pro Target Place:</strong> {{ $ticket->pro_target_place }}</p>
      @endif
      @if($ticket->pro_target_room)
      <p><strong>Pro Target Room:</strong> {{ $ticket->pro_target_room }}</p>
      @endif
      @if($ticket->current_tel_name)
      <p><strong>Current Tel Name:</strong> {{ $ticket->current_tel_name }}</p>
      @endif
      @if($ticket->new_tel_name)
      <p><strong>New Tel Name:</strong> {{ $ticket->new_tel_name }}</p>
      @endif
      @if($ticket->new_tel_number)
      <p><strong>New Tel Number:</strong> {{ $ticket->new_tel_number }}</p>
      @endif
      @if($ticket->bbb_subject)
      <p><strong>BBB Subject:</strong> {{ $ticket->bbb_subject }}</p>
      @endif
      @if($ticket->bbb_username)
      <p><strong>BBB Username:</strong> {{ $ticket->bbb_username }}</p>
      @endif
      @if($ticket->vtiger_subject)
      <p><strong>VTiger Subject:</strong> {{ $ticket->vtiger_subject }}</p>
      @endif
      @if($ticket->vtiger_username)
      <p><strong>VTiger Username:</strong> {{ $ticket->vtiger_username }}</p>
      @endif
      @if($ticket->smt_subject)
      <p><strong>SMT Subject:</strong> {{ $ticket->smt_subject }}</p>
      @endif
      @if($ticket->smt_username)
      <p><strong>SMT Username:</strong> {{ $ticket->smt_username }}</p>
      @endif
      @if($ticket->firmen_subject)
      <p><strong>Firmen Subject:</strong> {{ $ticket->firmen_subject }}</p>
      @endif
      @if($ticket->firmen_username)
      <p><strong>Firmen Username:</strong> {{ $ticket->firmen_username }}</p>
      @endif
      @if($ticket->terminal_name)
      <p><strong>Terminal Name:</strong> {{ $ticket->terminal_name }}</p>
      @endif
      @if($ticket->terminal_datev)
      <p><strong>Terminal Datev:</strong> {{ $ticket->terminal_datev }}</p>
      @endif
      @if($ticket->terminal_lexware)
      <p><strong>Terminal Lexware:</strong> {{ $ticket->terminal_lexware }}</p>
      @endif
      @if($ticket->terminal_expiry)
      <p><strong>Terminal Expiry:</strong> {{ $ticket->terminal_expiry }}</p>
      @endif
      @if($ticket->forward_on)
      <p><strong>Forward On:</strong> {{ $ticket->forward_on }}</p>
      @endif
      @if($ticket->forward_from)
      <p><strong>Forward From:</strong> {{ $ticket->forward_from }}</p>
      @endif
      @if($ticket->forward_required_at)
      <p><strong>Forward Required At:</strong> {{ $ticket->forward_required_at }}</p>
      @endif
      @if($ticket->cancelForward)
      <p><strong>Cancel Forward:</strong> {{ $ticket->cancelForward }}</p>
      @endif
      @if($ticket->notizen)
      <p><strong>Beschreibung:</strong> {!! ($ticket->notizen) !!}</p>
      @endif
    </div>
    @endforeach
  </div>
</body>

</html>