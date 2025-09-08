<div class="col-sm-12 invoice-col">
  <h4 class="text-bold mt-2" style="color:#004873">Sonnenschutz</h3>
</div>
@if($handwerk->lamellenvorhang)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Lamellenvorhang <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->lamellenvorhang_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->rollo)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Rollo <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->rollo_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
