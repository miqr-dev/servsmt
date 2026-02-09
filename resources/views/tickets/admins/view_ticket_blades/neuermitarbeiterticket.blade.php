<div class="invoice col-md-12 p-3 mb-3">
  <div class="row text-center">
    <div class="col-12">
      <h4 class="ticket_header">{{$ticket->problem_type}}</h4>
    </div>
  </div>
  <div class="row col-sm-12 flex justify-content-between my-3">
    <input class="col-sm-3 form-control" type="text" id="employee_username" name="employee_username"
      placeholder="Benutzername" value="{{ @$ticket->employee_username ? $ticket->employee_username : '' }}">
    <input class="col-sm-3 form-control" type="text" id="employee_password" name="employee_password" value="Miqr.2026#"
      readonly>
    <input class="col-sm-3 form-control" type="text" id="employee_email" name="employee_email" placeholder="Email"
      value="{{ @$ticket->employee_email ? $ticket->employee_email : '' }}">
  </div>
  <div class="row invoice-info mt-3">
    <div class="col-sm-4 invoice-col">
      Berechtigungen wie bei
      <address>
        <u class="mt-1"><strong>{{@$ticket->replication->name}}, {{@$ticket->replication->vorname}} </strong></u><br>
      </address>
    </div>
    <div class="{{ $ticket->employee_finish_at ? 'col-sm-4' : 'col-sm-8' }} invoice-col">
      Beginnt am
      <address>
        <u class="mt-1"><strong>{{@$ticket->employee_required_at->format('d-m-Y')}}</strong></u><br>
      </address>
    </div>
    @if($ticket->employee_finish_at)
    <div class="col-sm-4 invoice-col">
      Endet zum
      <address>
        <u class="mt-1"><strong style="color: rgba(255, 0, 0, 0.6);">{{ @$ticket->employee_finish_at->format('d-m-Y')
            }}</strong></u><br>
      </address>
    </div>
    @endif

    <div class="col-sm-4 invoice-col">
      Name
      <address>
        <u class="mt-1"><strong>{{@$ticket->employee_lastname}}, {{@$ticket->employee_firstname}}</strong></u><br>
      </address>
    </div>
    <div class="col-sm-4 invoice-col">
      Standort
      <address>
        <u class="mt-1"><strong>{{@$ticket->location->place->pnname}}, {{@$ticket->location->address}}</strong></u><br>
      </address>
    </div>
    <div class="col-sm-4 invoice-col">
      Telefon
      <address>
        <u class="mt-1"><strong>{{@$ticket->telephone_employee}}</strong></u><br>
      </address>
    </div>
    <div class="col-sm-4 invoice-col">
      Position
      <address>
        <u class="mt-1"><strong>{{@$ticket->position_employee}}</strong></u><br>
      </address>
    </div>
    <div class="col-sm-4 invoice-col">
      Abteilung
      <address>
        <u class="mt-1"><strong>{{@$ticket->abteilung_employee}}</strong></u><br>
      </address>
    </div>
    @if($ticket->isplus)
    <div class="col-sm-12 invoice-col">
      <strong>IS+</strong> <i class="fas fa-check" style="color:green;"></i><br>
    </div>
    @endif
    @if($ticket->outlook)
    <div class="col-sm-12 invoice-col">
      <strong>Outlook</strong> <i class="fas fa-check" style="color:green;"></i><br>
    </div>
    @endif
    <hr />
    @if($ticket->email_berlin)
    <div class="col-sm-3 invoice-col">
      <strong>Berlin</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_erfurt)
    <div class="col-sm-3 invoice-col">
      <strong>Erfurt</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_suhl)
    <div class="col-sm-3 invoice-col">
      <strong>Suhl</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_leipzig)
    <div class="col-sm-3 invoice-col">
      <strong>Leipzig</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_dresden)
    <div class="col-sm-3 invoice-col">
      <strong>Dresden</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_chemnitz)
    <div class="col-sm-3 invoice-col">
      <strong>Chemnitz</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_lasch)
    <div class="col-sm-3 invoice-col">
      <strong>Frau Lasch</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_lorenz)
    <div class="col-sm-3 invoice-col">
      <strong>Herr Lorenz</strong> <i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif
    @if($ticket->email_custom)
    <div class="col-sm-3 invoice-col">
      <strong>{{@$ticket->email_custom}}</strong><i class="fas fa-check" style="color:green;"></i>
    </div>
    @endif