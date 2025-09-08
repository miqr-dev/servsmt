<div class="modal fade" id="addRoom" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Raum hinzufügen</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
      <div class="modal-body">
      <form action="{{ route('addRoom')}}" method="POST">
        @csrf
          <div class="form-group">
            <label for="pnname">Adresse</label>
            <select class="form-control" id="settings_AddressCityList" name="pnname"></select>
            <small class="form-text text-muted">Bitte wählen Sie eine Adresse, um ein neues Zimmer hinzuzufügen.</small>
          </div>
          <div class="form-group d-flex justify-content-center">
            <input type="text" class="form-control mx-1" name="rname" id="settings_rname" placeholder="Raumnummer" required>
            <input type="text" class="form-control mx-1" name="etage" id="settings_etage" placeholder="Etage" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="altrname" id="settings_altrname" placeholder="Alternat.Raumname" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Einreichen</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
        </div>
      </form>
    </div>
  </div>
</div>