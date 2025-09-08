
  <div class="invoice col-md-12 p-3 mb-3">
    <div class="row text-center">
      <div class="col-12">
        <h4 class="ticket_header">{{$ticket->problem_type}}</h4>
      </div>
    </div>
    <div class="row invoice-info">
      <div class="col-sm-12 invoice-col">
        Rechner
        <address>
          <u class="mt-1"><strong>{{@$ticket->invitem->gname}}</strong></u><br>
        </address>
      </div>
      @if($ticket->keyboard)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Tastatur</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->mouse)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Maus</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->speaker)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Lautsprecher</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->headset)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Kopfhörer</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->webcam)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Webcam</strong></u><br>
        </address>
      </div>
      @endif
      @if($ticket->monitor)
      <div class="col-sm-4 invoice-col">
        <address>
          <u class="mt-1"><strong>Bildschirm</strong></u><br>
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