<div class="modal fade" id="inventur" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width: 1080px!important;" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Inventur</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
            <div class="modal-body">
                <div class="form-row mb-3">
                    <div class="form-group col-md-4">
                        <select id="location_id_inventur" name="location_id" class="form-control" required>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <select id="room_id_inventur" name="room_id" class="form-control" required>
                        </select>
										</div>
										<div class="form-group col-md-4">
											<input type="text" id="inventur_check_input" class="form-control" onblur="this.focus()" style="opacity: 1;" autofocus>  
										</div>
									</div>
                  

                  <div class="d-flex flex-row">
                    <div class="d-flex justify-content-around col-lg-12">
                      <div class="card card-primary card-outline col-lg-12">
                        <div class="card-body" style="overflow-y: hidden;">
                          <div class="row mx-auto table_inventur">

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

            </div>
            <div class="modal-footer">
              <button type="submit" id="inventur_submit" class="btn btn-success">Einfugen</button>
              <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
            </div>
        </div>
    </div>
</div>
