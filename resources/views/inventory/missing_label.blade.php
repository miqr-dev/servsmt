<div class="modal fade" id="missing_label" data-keyboard="false" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Druck Fehlende Inventarnummern</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="form-group col-lg-12">
              <label for="searchgerate"> Welcher Rechner</label>
              <select class="custom-select form-control searchgerate" id="searchgerate" name="searchgerate" required>
              <option class="form-control" value="-1"></option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success hidden-print" onclick="printmissingfunction()"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>Drucken</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
        </div>
      </div>
    </div>
  </div>





  