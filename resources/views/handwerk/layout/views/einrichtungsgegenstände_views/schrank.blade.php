<div class="col-sm-12 invoice-col">
  <h4 class="text-bold mt-2" style="color:#004873">Tafel</h3>
</div>
@if($handwerk->rollcontainer)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">rollcontainer <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->rollcontainer_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->standcontainer)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">standcontainer <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->standcontainer_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->hochschrank)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Hochschrank <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->hochschrank_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->ordnerhöhen_2)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Ordnerhöhen 2 <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->ordnerhöhen_2_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->ordnerhöhen_3)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Ordnerhöhen 3 <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->ordnerhöhen_3_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->hängeschrank)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Hängeschrank <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->hängeschrank_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif