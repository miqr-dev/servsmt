<div class="modal fade" id="add" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 1080px!important;"  role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Formular Erfassung</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="{{ route('item.store') }}" method="POST" id="item_form">
          @csrf
          <!-- First row -->
          <div class="form-row mb-3">
              <div class="form-group col-md-4">
                <label for="invnr" data-toggle="tooltip" data-placement="top" title="Pflichtfeld" >Inventarnummer
                  <i class="fas fa-asterisk" style="color:#d9534f;"></i>
                </label>
                <input type="text" class="form-control invnr" name="invnr" readonly required>
              </div>
              <div class="form-group col-md-4">
                <label for="andat" data-toggle="tooltip" data-placement="top" title="Pflichtfeld">Anschaffungsdatum
                  <i class="fas fa-asterisk"style="color:#d9534f;"></i>
                </label>
                <input type="text" class="form-control date" name="andat" required>
              </div>
              <div class="form-group col-md-4">
                <label for="kp" data-toggle="tooltip" data-placement="top" title="Pflichtfeld">Kaufpreis
                  <i class="fas fa-asterisk" style="color:#d9534f;"></i>
                </label>
                <input type="text" class="form-control kaufpreis"  name="kp" required>
              </div>
          </div>
          <!-- End of First row-->
          <!-- Second row-->
          <div class="form-row mb-3">
              <div class="form-group col-md-4">
                <label for="location_id" data-toggle="tooltip" data-placement="top" title="Pflichtfeld">Standort
                  <i class="fas fa-asterisk" style="color:#d9534f;"></i>
                </label>
                <select id="location_id" name="location_id" class="form-control" required></select>
              </div>
              <div class="form-group col-md-4">
                <label for="room_id" data-toggle="tooltip" data-placement="top" title="Pflichtfeld">Raum
                  <i class="fas fa-asterisk" style="color:#d9534f;"></i>
                </label>
                <select id="rooms" name="room_id" class="form-control" required></select>
              </div>
          </div>
          <!-- End of Second row-->
          <!-- Third row-->
          <div class="form-row mb-3">
              <div class="form-group col-md-3">
                <label for="gname" data-toggle="tooltip" data-placement="top" title="Pflichtfeld">Gerätename
                  <i class="fas fa-asterisk" style="color:#d9534f;"></i>
                </label>
                <input type="text" class="form-control gname req" id="gname" name="gname" required>
              </div>
              <div class="form-group col-md-3">
                <label for="gart_id" data-toggle="tooltip" data-placement="top" title="Pflichtfeld">Geräteart
                  <i class="fas fa-asterisk" style="color:#d9534f;"></i>
                </label>
                <select id="gart_id" name="gart_id" class="form-control" required></select>
              </div>
              <div class="form-group col-md-3">
                <label for="gtyp" data-toggle="tooltip" data-placement="top" title="Modell z.B HP, Samsung usw">Gerätetyp
                  <i class="fas fa-info-circle" style="color:dodgerblue"></i>
                </label>
                <input type="text" class="form-control gtyp" id="gtyp" name="gtyp">
              </div>
              <input type="hidden" class="form-control path_to_rg" id="path_to_rg" name="path_to_rg">
              <div class="form-group col-md-3">
                <label for="gtyp">Seriennummer</label>
                <input type="text" class="form-control sn" id="sn" name="sn">
              </div>
          </div>
          <!-- End of Third row-->
          <!-- Forth row -->
          <div class="form-row">
            <div class="form-group col-md-12">
              <label for="notes">Notizen</label>
              <textarea class="form-control notes" id="notes" name="notes" rows="3"></textarea>
            </div>
          </div>
          <!-- End of Forth row -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success submit_form_ajax" style="visibility:hidden;">Einfügen</button>
        <button type="submit" class="btn btn-success submit_form">Einfügen</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
      </div>
  </form>
         <div class="panel-body">
          <form id="dropzoneForm" class="dropzone" action="{{ route('dropzone.upload_pdf') }}">
            @csrf
          </form>
        </div>
      <br />
      <div class="panel panel-default">
        <div class="panel-body" id="uploaded_pdf">
        </div>
      </div>
  </div>
  </div>
</div>
