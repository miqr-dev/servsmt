<div class="modal fade" id="addCity" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Stadt hinzufügen</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form action="/create_city" method="POST">
				@csrf
      <div class="modal-body">
        <input type="text" name="pnname" class="form-control">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Einreichen</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
			</div>
		</form>
    </div>
  </div>
</div>
