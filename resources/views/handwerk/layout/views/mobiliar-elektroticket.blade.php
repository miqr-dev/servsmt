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
    @if(@$handwerk->kühlschrank ||$handwerk->geschirrspüler|| $handwerk->kaffeemaschine || $handwerk->ventilator)
    <div>
      <h4 class="text-bold mt-2" style="color:#004873">Elektro</h3>
    </div>
    @endif
    @if($handwerk->kühlschrank)
    <div class="col-sm-4 invoice-col text-bold">
      <address>
        <spam class="mt-1">Kühlschrank <i class="fa-solid fa-arrow-right-long"></i>
          <spam style="color:#008e5e;">{{@$handwerk->kühlschrank_qty}}</spam>
        </spam><br>
      </address>
    </div>
    @endif
    @if($handwerk->geschirrspüler)
    <div class="col-sm-4 invoice-col text-bold">
      <address>
        <spam class="mt-1">Geschirrspüler <i class="fa-solid fa-arrow-right-long"></i>
          <spam style="color:#008e5e;">{{@$handwerk->geschirrspüler_qty}}</spam>
        </spam><br>
      </address>
    </div>
    @endif
    @if($handwerk->kaffeemaschine)
    <div class="col-sm-4 invoice-col text-bold">
      <address>
        <spam class="mt-1">Kaffeemaschine <i class="fa-solid fa-arrow-right-long"></i>
          <spam style="color:#008e5e;">{{@$handwerk->kaffeemaschine_qty}}</spam>
        </spam><br>
      </address>
    </div>
    @endif
    @if($handwerk->ventilator)
    <div class="col-sm-4 invoice-col text-bold">
      <address>
        <spam class="mt-1">Ventilator <i class="fa-solid fa-arrow-right-long"></i>
          <spam style="color:#008e5e;">{{@$handwerk->ventilator_qty}}</spam>
        </spam><br>
      </address>
    </div>
    @endif