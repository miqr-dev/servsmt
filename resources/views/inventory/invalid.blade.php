<div class="modal fade" id="invalid" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 1080px!important;"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Formular Ausmusterung Inventargut</h5>
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
												<input type="search" name="search_edit" placeholder="Suchen" id="search_amg" class="form-control py-2 border-left-0 border-right-0 border rounded"
																value="{{ request()->input('search') }}">
												<span class="input-group-append">
													<div class="input-group-text bg-transparent border-left-0"><i class="fas fa-ellipsis-h" style="color: #0275d8;" id="chksrch"></i></div>
											</span>
										</div>
									</div>
								</div>
                <div class="container">
                    <!-- Main row -->
                    <div class="row">
                        <div class="col-lg-12 mt-2">
                        <form action="{{ route('invalid') }}" method="POST" class="amg_form">
                            @csrf
                            <!-- First row -->
                            <div class="form-row mb-3">
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control gname_amg" placeholder="Gerätename" data-toggle="tooltip" data-placement="top" title="Gerätename" readonly>
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control inventarnummer_amg" name="invnr" placeholder="Inventarnummer" readonly
                                    data-toggle="tooltip" data-placement="top" title="Inventarnummer">
                                </div>

                                <div class="form-group col-md-3">
                                        <input type="text" class="form-control andat_amg" name="andat" value="" placeholder="Anschaffungsdatum" data-toggle="tooltip" data-placement="top" title="Anschaffungsdatum" readonly>
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control kp_amg" placeholder="Kaufpreis" readonly
                                    data-toggle="tooltip" data-placement="top" title="Kaufpreis">
                                </div>
                            </div>
                            <!-- Second row-->
                            <div class="form-row mb-3">
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control standort_amg" placeholder="Standort" data-toggle="tooltip" data-placement="top" title="Standort" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control raum_amg" placeholder="Raum" data-toggle="tooltip" data-placement="top" title="Raum" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <select id="grund" name="grund" class="form-control" data-toggle="tooltip" data-placement="top" title="Grund" required>
                                    </select>
                                </div>
                            </div>
                            <!-- Third row-->
                            <div class="form-row mb-4">
                                <div class="form-group col-md-3">
                                    <input type="text" class="form-control gart_amg" placeholder="Geräteart" data-toggle="tooltip" data-placement="top" title="Geräteart" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                        <input type="text" class="form-control gtyp_amg" placeholder="Gerätetyp" data-toggle="tooltip" data-placement="top" title="Gerätetyp" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control sn_amg" placeholder="Seriennummer" readonly
                                    data-toggle="tooltip" data-placement="top" title="Seriennummer">
                                </div>
                            </div>
                            <!-- Forth row -->
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <textarea class="form-control notes_amg" name="notes" rows="3" placeholder="Notizen" data-toggle="tooltip" data-placement="top" title="Notizen"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Ausmustern</button>
              <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
            </div>
        </form>
        </div>
    </div>
</div>



