<div class="col-sm-12 invoice-col">
  <h4 class="text-bold mt-2" style="color:#004873">Küche</h3>
</div>
@if($handwerk->barzeile)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Barzeile <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->barzeile_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->bar_Hochstühle)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Bar Hochstühle <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->bar_Hochstühle_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->küchenzeile)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">küchenzeile <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->küchenzeile_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
