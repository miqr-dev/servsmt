<div class="col-sm-12 invoice-col">
  <h4 class="text-bold mt-2" style="color:#004873">Tafel</h3><br>
</div>
@if($handwerk->schiebetafel)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Schiebetafel <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->schiebetafel_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->whiteboard)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Whiteboard <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->whiteboard_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->kreidetafel)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Kreidetafel <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->kreidetafel_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->pinnwand)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Pinnwand <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->pinnwand_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif