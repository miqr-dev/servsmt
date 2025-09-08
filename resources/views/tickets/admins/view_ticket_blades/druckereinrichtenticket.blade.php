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
      <div class="col-sm-4 invoice-col">
        Drucker
        <address>
          <u class="mt-1"><strong>{{@$ticket->printer->gname}}</strong></u><br>
        </address>
      </div>
      <div class="col-sm-4 invoice-col">
        Adresse
        <address>
          <u class="mt-1"><strong>{{@$ticket->printer->invroom->location->address}}</strong></u><br>
        </address>
      </div>
      <div class="col-sm-4 invoice-col">
        Raum
        <address>
          <u class="mt-1"><strong>{{@$ticket->printer->invroom->rname}} <i class="fas fa-grip-lines-vertical"></i> {{@$ticket->printer->invroom->altrname}}</strong></u><br>
        </address>
      </div>