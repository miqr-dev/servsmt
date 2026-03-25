<div class="invoice col-md-12 p-3 mb-3">
  <div class="row text-center">
    <div class="col-12">
      <h4 class="ticket_header">{{$ticket->problem_type}}</h4>
    </div>
  </div>

  <div class="row invoice-info">
    <div class="col-sm-4 invoice-col">
      PCs
      <address>
        @forelse($ticket->pcs as $pc)
          <u class="mt-1 d-inline-block"><strong>{{ $pc->gname ?: $pc->invnr }}</strong></u><br>
        @empty
          @if(!empty($ticket->invitem))
            <u class="mt-1 d-inline-block"><strong>{{ $ticket->invitem->gname ?: $ticket->invitem->invnr }}</strong></u>
          @else
            <u class="mt-1"><strong>—</strong></u>
          @endif
        @endforelse
      </address>
    </div>
    <div class="col-sm-4 invoice-col">
      Neue Adresse
      <address>
        <u class="mt-1"><strong>{{@$telNewAddress->address}}</strong></u><br>
      </address>
    </div>
    <div class="col-sm-4 invoice-col">
      Neuer Raum
      <address>
        <u class="mt-1"><strong>{{@$telNewRoom->rname}} <i class="fas fa-grip-lines-vertical"></i> {{@$telNewRoom->altrname}} </strong></u><br>
      </address>
    </div>
  </div>
</div>
