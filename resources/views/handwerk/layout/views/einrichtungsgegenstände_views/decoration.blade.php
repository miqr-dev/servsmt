<div class="col-sm-12 invoice-col">
  <h4 class="text-bold mt-2" style="color:#004873">Dekoration</h3>
</div>
@if($handwerk->bilder)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Bilder <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->bilder_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
