
<div class="modal fade" id="move" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="max-width: 1080px!important;"  role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Formular Bewegen</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			<!-- Search -->								
			<div class="row mb-4 ">
				<div class="col-md-6 offset-3">
					<div class="input-group">
							<span class="input-group-prepend">
								<div class="input-group-text bg-transparent border-right-0"><i class="fa fa-search" style="color: #0275d8;"></i></div>
							</span>
							<input type="search" name="search_move" placeholder="Suchen" id="search_move" class="form-control py-2 border-left-0 border-right-0 border rounded"
											value="{{ request()->input('search') }}">
							<span class="input-group-append">
								<div class="input-group-text bg-transparent border-left-0"><i class="fas fa-ellipsis-h" style="color: #0275d8;" id="chksrchmove"></i></div>
						</span>
					</div>
				</div>
			</div>
			<form action=" {{route('item_move_store')}} " method="POST" class="move_form">
			@csrf
			<div class="col-md-12 form-inline">
				<input type="text" name="address" class="form-control mr-sm-1 col-md-3 move_address" placeholder="Adresse" readonly>
				<input type="text" name="raum" class="form-control col-md-2 move_raum" placeholder="Raum" readonly>
        <input type="hidden" name="old_room_id_move" class="old_room_id_move" value="" required>
				<div class="form-check col-md-1">
						<i class="fa fa-arrow-right form_control" style="color: #5cb85c;"></i>
				</div>
				<select id="location_id_move" name="location_id_move" class="form-control mr-sm-1 col-md-3" required>
				</select>
				<select id="room_id_move" name="room_id_move" class="form-control mr-sm-1 col-md-2" required>
				</select>
			</div>
			<input type="hidden" name="gname_move" class="gname_move">
			</div>
			<div class="modal-footer">
        <button type="submit" class="btn btn-success submit_form_move">Bewegen</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
      </div>
			</form>
		</div>
	</div>
</div>