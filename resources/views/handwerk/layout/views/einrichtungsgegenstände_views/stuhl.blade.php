<div class="col-sm-12 invoice-col">
  <h4 class="text-bold mt-2" style="color:#004873">Stuhl</h3>
</div>
@if($handwerk->schreibtischstuhl)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Schreibtischstuhl <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->schreibtischstuhl_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->bürostuhl)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Bürostuhl <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->bürostuhl_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->stapelstühl)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Stapelstühl <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->stapelstühl_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
