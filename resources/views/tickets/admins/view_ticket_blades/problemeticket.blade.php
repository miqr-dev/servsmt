  <div class="invoice col-md-12 p-3 mb-3">
    <div class="row text-center">
      <div class="col-12">
        <h4 class="ticket_header">{{$ticket->problem_type}}</h4>
      </div>
    </div>
    <div class="row invoice-info">
      <div class="col-sm-4 invoice-col">
        Rechner
        <address>
          <u class="mt-1"><strong>{{@$ticket->invitem->gname}}</strong></u><br>
        </address>
      </div>
      <div class="col-sm-4 invoice-col">
        Adresse
        <address>
          <u class="mt-1"><strong>{{@$ticket->invitem->invroom->location->address}}</strong></u><br>
        </address>
      </div>
      <div class="col-sm-4 invoice-col">
        Raum
        <address>
          <u class="mt-1"><strong>{{@$ticket->invitem->invroom->rname}} <i class="fas fa-grip-lines-vertical"></i> {{@$ticket->invitem->invroom->altrname}}</strong></u><br>
        </address>
      </div>
      <div class="col-md-12 invoice-col">
        <strong style="color:#661421;">Probleme <i class="fas fa-exclamation-triangle  fa-lg"></i></strong> 
        <!-- <address>
          <strong>{{@$ticket->notizen}}</strong><br>
        </address> -->
      </div>
      @if($ticket->geht_nicht_an)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Geht nicht an</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->blue)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Geht an / Blue Screen</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->black)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Geht an / Black Screen</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->slow_computer)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Sehr Langsam</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->web_cam_problem)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Webcam funktioniert nicht</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->head_set_problem)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Headset funktioniert nicht</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->lautsprecher_mal)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Lautsprecher funktioniert nicht</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->keyboard_malfunction)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Tastatur funktioniert nicht</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->mouse_mal)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Maus funktioniert nicht</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->slow_network)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Netzwerkzugriff langsam</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->no_network_drive)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Keine Netzlaufwerke</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->laud_fan)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>lautes Lüftergeräusch</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->other)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Sonstiges</strong></u><br>
        </address>
      </div>
      @endif
      