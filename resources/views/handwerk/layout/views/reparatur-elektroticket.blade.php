<div class="invoice col-md-12 p-3 mb-3">
  <div class="row text-center">
    <div class="col-12">
      <h4 class="ticket_header">{{$handwerk->problem_type}}</h4>
    </div>
  </div>
  <div class="row invoice-info">
    <div class="col-sm-6 invoice-col">
      Adresse
      <address>
        <u class="mt-1"><strong>{{$handwerk->location->address}}</strong></u><br>
      </address>
    </div>
    @if(@$handwerk->room)
    <div class="col-sm-6 invoice-col">
      Raum
      <address>
        <u class="mt-1"><strong>{{@$handwerk->room->rname}} <i class="fas fa-grip-lines-vertical"></i>
            &nbsp;{{@$handwerk->room->altrname}}</strong></u><br>
      </address>
    </div>
    @endif
    @if(@$handwerk->custom_room)
    <div class="col-sm-6 invoice-col">
      <spam class="text-success">Raum</spam>
      <address>
        <u class="mt-1"><strong>{{@$handwerk->custom_room}} </strong></u><br>
      </address>
    </div>
    @endif
    <div class="col-sm-6 invoice-col">
      <spam class="text-success">Betreff</spam>
      <address>
        <u class="mt-1"><strong>{{@$handwerk->subject}} </strong></u><br>
      </address>
    </div>