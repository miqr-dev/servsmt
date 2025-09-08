<div class="modal fade" id="edit" role="dialog" data-keyboard="false" data-backdrop="static" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="max-width: 1080px!important;"  role="document">
		<div class="modal-content">
				<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Formular Änderung</h5>
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
                <input type="search" name="search_edit" placeholder="Suchen" id="search_edit" class="form-control py-2 border-left-0 border-right-0 border rounded"
                        value="{{ request()->input('search')}}">
                <div id="theList"></div>
                @csrf
                <span class="input-group-append">
                  <div class="input-group-text bg-transparent border-left-0"><i class="fas fa-ellipsis-h" style="color: #0275d8;" id="chksrchedit"></i></div>
              </span>
            </div>
          </div>
        </div>
					<form action="{{ route('item.update') }}" method="POST" class="item_edit_form">
					@csrf
					@method('PATCH')
					<!-- First row -->
					<div class="form-row mb-3">
							<div class="form-group col-md-4">
									<input type="text" class="form-control invnr_edit" name="invnr" placeholder="Inventarnummer" data-toggle="tooltip" data-placement="top" title="Inventarnummer" readonly>
							</div>
							<div class="form-group col-md-4">
									<input type="text" class="form-control invnr_id" name="invid" placeholder="id" data-toggle="tooltip" data-placement="top" title="Inventarnummer" readonly>
							</div>
							<div class="form-group col-md-4">
									<input type="text" class="form-control andat_edit" name="andat" value=""  placeholder="Anschaffungsdatum" data-toggle="tooltip" data-placement="top" title="Anschaffungsdatum" readonly>
							</div>
							<div class="form-group col-md-4">
									<input type="text" class="form-control gname_edit"  name="gname" placeholder="Gerätename" data-toggle="tooltip" data-placement="top" title="Gerätename" readonly>
							</div>
					</div>
					<!-- Second row-->
					<div class="form-row mb-3">
							<div class="form-group col-md-4">
									<input type="text" class="form-control standort_edit"  name="location_id" placeholder="Standort" data-toggle="tooltip" data-placement="top" title="Standort" readonly>
							</div>
							<div class="form-group col-md-4">
									<input type="text" class="form-control raum_edit"  name="room_id" placeholder="Raum" data-toggle="tooltip" data-placement="top" title="Raum" readonly>
							</div>
							<div class="form-group col-md-2">
									<input type="text" class="form-control kp_edit"  name="kp" placeholder="Kaufpreis" data-toggle="tooltip" data-placement="top" title="Kaufpreis" readonly>
							</div>
							<div class="form-group col-md-2">
									<div class="ml-5">
											<a href="" target="_blank" class="pdf_edit_green" style="display: none;">
												<i class="far fa-file-pdf fa-4x" style="color:#F40F02" data-toggle="tooltip" data-placement="top" title="Rechnung ansehen"></i>
											</a>
											<i class="far fa-file-pdf fa-4x pdf_edit_red" style="color:gray" data-toggle="tooltip" data-placement="top" title="Keine Rechnung"></i>
									</div>
							</div>
					</div>
					<!-- Third row-->
					<div class="form-row mb-3">
							<div class="form-group col-md-3">
									<input type="text" class="form-control gart_edit"  name="gart_id" placeholder="Geräteart" data-toggle="tooltip" data-placement="top" title="Geräteart" readonly>
							</div>
							<div class="form-group col-md-3">
											<input type="text" class="form-control gtyp_edit" name="gtyp" placeholder="Gerätetyp" data-toggle="tooltip" data-placement="top" title="Gerätetyp" readonly>
							</div>
									<input type="hidden" class="form-control path_to_rg" name="path_to_rg">
							<div class="form-group col-md-3">
									<input type="text" class="form-control sn_edit" name="sn" placeholder="Seriennummer" data-toggle="tooltip" data-placement="top" title="Seriennummer" readonly>
							</div>
              <div class="form-group col-md-3 barcode">
                  <input type="text" class="form-control invnr_print input-lg" readonly>
              </div>
					</div>
					<!-- Forth row -->
					<div class="form-row">
							<div class="form-group col-md-12" style="text-align: center;">
									<textarea class="form-control notes_edit" name="notes" rows="3" placeholder="Notizen" data-toggle="tooltip" data-placement="top" title="Notizen"></textarea required>
							</div>
					</div>
					<!-- End of Forth row -->
					</div>
					<div class="modal-footer">
            <button type="submit" id="submit_form_edit" class="btn btn-success submit_form_edit">Ändern</button>
							<button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
					</div>
				</form>
		</div>
	</div>
</div>