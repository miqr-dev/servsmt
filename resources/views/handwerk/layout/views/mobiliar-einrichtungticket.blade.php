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
        <u class="mt-1"><strong>{{@$handwerk->room->rname}}<i class="fas fa-grip-lines-vertical"></i>
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
    @if($handwerk->schiebetafel || $handwerk->whiteboard || $handwerk->kreidetafel|| $handwerk->pinnwand)
    @include('handwerk.layout.views.einrichtungsgegenstände_views.tafel')
    @endif
    @if($handwerk->schreibtisch_TN_80x160 || $handwerk->schreibtisch_TN_70x70 || $handwerk->schreibtisch_TN_80x80 ||
    $handwerk->schreibtisch_DOZ_80x140 ||$handwerk->schreibtisch_DOZ_80x160 ||$handwerk->schreibtisch_DOZ_80x180 ||
    $handwerk->schreibtisch_MA_80x140 || $handwerk->schreibtisch_MA_80x160 ||$handwerk->schreibtisch_MA_80x180 ||
    $handwerk->stehtisch || $handwerk->gesprächstisch_rund || $handwerk->konferenztisch || $handwerk->couchtisch ||
    $handwerk->beistelltisch)
    @include('handwerk.layout.views.einrichtungsgegenstände_views.tisch')
    @endif
    @if($handwerk->schreibtischstuhl || $handwerk->bürostuhl || $handwerk->stapelstühl)
    @include('handwerk.layout.views.einrichtungsgegenstände_views.stuhl')
    @endif
    @if($handwerk->rollcontainer || $handwerk->standcontainer || $handwerk->hochschrank ||$handwerk->ordnerhöhen_2 ||
    $handwerk->ordnerhöhen_3 || $handwerk->hängeschrank)
    @include('handwerk.layout.views.einrichtungsgegenstände_views.schrank')
    @endif
    @if($handwerk->lamellenvorhang || $handwerk->rollo )
    @include('handwerk.layout.views.einrichtungsgegenstände_views.sonnenschutz')
    @endif
    @if($handwerk->bilder)
    @include('handwerk.layout.views.einrichtungsgegenstände_views.decoration')
    @endif
    @if($handwerk->handtuchspender || $handwerk->toilettenpapierhalter || $handwerk->desinfektionsmittelspender)
    @include('handwerk.layout.views.einrichtungsgegenstände_views.wc')
    @endif
    @if($handwerk->barzeile || $handwerk->bar_Hochstühle || $handwerk->küchenzeile)
    @include('handwerk.layout.views.einrichtungsgegenstände_views.küche')
    @endif