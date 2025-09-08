<div class="invoice col-md-12 p-3 mb-3">
  <div class="row text-center">
    <div class="col-12">
      <h4 class="ticket_header">{{@$ticket->problem_type}}</h4>
    </div>
  </div>
  <div class="row invoice-info">
    <div class="col-sm-6 invoice-col">
      E-Mails an folgenden Empfänger weiterleiten
      <address>
        <u class="mt-1"><strong>{{@$ticket->forward_on}}</strong></u><br>
      </address>
      Von:
      <address>
        <u class="mt-1"><strong>{{@$ticket->forward_from}}</strong></u><br>
      </address>
      Ab
      <address>
        <u class="mt-1"><strong>{{\Carbon\carbon::parse(@$ticket->forward_required_at)->format('d-m-Y') }} </strong></u><br>
      </address>
    </div>
    @if($ticket->cancelForward)
    <div class="col-sm-4 invoice-col">
      <address>
        <h4 class="mt-1"><strong style="color: red;">Weiterleitung aufheben</strong></h4><br>
      </address>
    </div>
    @endif