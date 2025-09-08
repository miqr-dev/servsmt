<div class="col-sm-12 invoice-col">
  <h4 class="text-bold mt-2" style="color:#004873">WC</h3>
</div>
@if($handwerk->handtuchspender)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Handtuchspender <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->handtuchspender_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->toilettenpapierhalter)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Toilettenpapierhalter <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->toilettenpapierhalter_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif
@if($handwerk->desinfektionsmittelspender)
<div class="col-sm-4 invoice-col text-bold">
  <address>
    <spam class="mt-1">Dr4esinfektionsmittelspender <i class="fa-solid fa-arrow-right-long"></i>
      <spam style="color:#008e5e;">{{@$handwerk->desinfektionsmittelspender_qty}}</spam>
    </spam><br>
  </address>
</div>
@endif