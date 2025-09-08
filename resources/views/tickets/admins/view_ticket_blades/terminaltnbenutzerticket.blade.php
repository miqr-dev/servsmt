<div class="invoice col-md-12 p-3 mb-3">
  <div class="row text-center">
    <div class="col-12">
      <h4 class="ticket_header">{{$ticket->problem_type}}</h4>
    </div>
  </div>
  <div class="row invoice-info">
    <div class="col-sm-4 invoice-col">
      Benutzername
      <address>
        <u class="mt-1"><strong>{{$ticket->terminal_name}}</strong></u><br>
      </address>
    </div>
    <div class="col-sm-4 invoice-col">
      Maßnahmeende
      <address>
        <u class="mt-1"><strong>{{$ticket->terminal_expiry}}</strong></u><br>
      </address>
    </div>
    <div class="col-md-12 invoice-col mb-1">

    </div>
    @if($ticket->terminal_datev)
    <div class="col-sm-4 invoice-col">
      <address>
        <u class="mt-1"><strong>Datev</strong> <i class="fas fa-check" style="color:green;"></i></u><br>
      </address>
    </div>
    @endif
    @if($ticket->terminal_lexware)
    <div class="col-sm-4 invoice-col">
      <address>
        <u class="mt-1"><strong> Lexware</strong> <i class="fas fa-check" style="color:green;"></i></u><br>
      </address>
    </div>
    @endif