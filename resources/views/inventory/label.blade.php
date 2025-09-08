<div class="modal fade" id="printpage" data-keyboard="false" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Formular Druck Inventarnummern</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

            <div class="form-row mb-3">
                <div class="form-group col-md-6">
                    <select id="address" class="form-control" required>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control inventNumber" id="prntinvnr" placeholder="Inventarnummer" readonly>
                </div>
                <div class="form-group col-md-2">
                    <input type="text" class="form-control" id="anzahl" placeholder="Anzahl" required>
                </div>
            </div>

        </div>
        <div class="modal-footer">
          <button class="btn btn-success hidden-print" onclick="printfunction()"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>Drucken</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
        </div>
      </div>
    </div>
  </div>





