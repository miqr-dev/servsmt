<div class="modal modal-right fade" id="role_view" tabindex="-1" role="dialog" aria-labelledby="right_modal_lg">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Berechtigungen</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <h5><i class="fas fa-arrow-alt-circle-right" style="color:#5bc0de;"></i> &nbsp;<em><u>Einstellungen</u></em></h5>
          </div><!--end first row-->
        </div>
        <div class="row mt-1">
            <div class="col-4">
              <h5>Inventur Einstellungen</h5>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="enter_city" class="custom-control-input settings-inventory-checkbox" id="enter_city" value="">
                <label class="custom-control-label" for="enter_city">Stadt hinzufügen</label>
              </div>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="enter_address" class="custom-control-input settings-inventory-checkbox" id="enter_address">
                <label class="custom-control-label" for="enter_address">Adresse hinzufügen</label>
              </div>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="enter_room" class="custom-control-input settings-inventory-checkbox" id="enter_room">
                <label class="custom-control-label" for="enter_room">Raum hinzufügen</label>
              </div>
            </div> <!-- col-4 -->
            <div class="col-4">
              <h5>Benutzereinstellungen</h5>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="manage_users" class="custom-control-input settings-user-checkbox" id="manage_users">
                <label class="custom-control-label" for="manage_users">Benutzerverwaltung</label>
              </div>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="manage_rolls" class="custom-control-input settings-user-checkbox" id="manage_rolls">
                <label class="custom-control-label" for="manage_rolls">Rollen</label>
              </div>
            </div> <!-- col-4 -->
        </div> <!-- /.row -->
        <hr>
        <div class="row mt-3">
          <div class="col-md-12">
            <h5><i class="fas fa-arrow-alt-circle-right" style="color:#f0ad4e;"></i> &nbsp;<em><u>Inventur</u></em></h5>
          </div><!--end third row-->
        </div>
        <div class="row mt-1">
          <div class="col-4">
            <h5 class="lead" style="color: #F08080;">Geräte</h5>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="view_machines_actuall" id="view_machines_actuall" class="custom-control-input">
              <label class="custom-control-label" for="view_machines_actuall">Aktuell</label>
            </div>
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" name="view_all_machines" id="view_all_machines" class="custom-control-input" readonly>
              <label class="custom-control-label" for="view_all_machines">Aktuell + Ausgemustert</label>
            </div>
          </div> <!-- col-2 -->
          <div class="col-4">
            <h5 class="lead" style="color:  #5bc0de;">Erfassen</h5>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="add_machine" id="add_machine" class="custom-control-input">
              <label class="custom-control-label" for="add_machine">Erfassen</label>
            </div>
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" name="add_machine_manually" id="add_machine_manually" class="custom-control-input">
              <label class="custom-control-label" for="add_machine_manually">Manuell Erfassen</label>
            </div>
          </div> <!-- col-2 -->
          <div class="col-4">
            <h5 class="lead" style="color: #007bff;">Drucken</h5>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="print_list" id="print_list" class="custom-control-input">
              <label class="custom-control-label" for="print_list">Listen</label>
            </div>
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" name="print_ticket" id="print_ticket" class="custom-control-input">
              <label class="custom-control-label" for="print_ticket">Etiketten</label>
            </div>
          </div> <!-- col-2 -->
          <div class="col-3">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" name="move_machine" id="move_machine" class="custom-control-input">
              <label class="custom-control-label" style="color: #5cb85c;" for="move_machine">Bewegen</label>
            </div>
          </div> <!-- col-2 -->
          <div class="col-3">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" name="delete_machine" id="delete_machine" class="custom-control-input">
              <label class="custom-control-label" style="color: #6c757d;" for="delete_machine">Ausmustern</label>
            </div>
          </div> <!-- col-2 -->
          <div class="col-3">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" name="edit_machine" id="edit_machine" class="custom-control-input">
              <label class="custom-control-label" style="color: #0275d8;" for="edit_machine">Ändern</label>
            </div>
          </div> <!-- col-2 -->
          <div class="col-3">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" name="access_inventory" id="access_inventory" class="custom-control-input">
              <label class="custom-control-label" style="color: orange;" for="access_inventory">Inventur</label>
            </div>
          </div> <!-- col-2 -->
      </div> <!-- /.row -->
      <hr>
      <div class="row mt-3">
        <div class="col-md-12">
          <h5><i class="fas fa-arrow-alt-circle-right" style="color: green"></i> &nbsp;<em><u>Standort</u></em></h5>
        </div><!--end third row-->
      </div>
      <div class="row mt-1">
        <div class="col-3">
          <h5 class="lead" style="color: #F08080;">Erfurt</h5>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="bhof_1" class="custom-control-input">
            <label class="custom-control-label" for="bhof_1">BHof 1</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="bhof_2" class="custom-control-input">
            <label class="custom-control-label" for="bhof_2">Bhof 2</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="bhof_4" class="custom-control-input">
            <label class="custom-control-label" for="bhof_4">Bhof 4</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="bhof_18" class="custom-control-input">
            <label class="custom-control-label" for="bhof_18">Bhof 18</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="h89" class="custom-control-input">
            <label class="custom-control-label" for="h89">H89</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="h92" class="custom-control-input">
            <label class="custom-control-label" for="h92">H92</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="otto_35" class="custom-control-input">
            <label class="custom-control-label" for="otto_35">Otto 35</label>
          </div>
        </div> <!-- End Erfurt -->
        <div class="col-3">
          <h5 class="lead" style="color:  #5bc0de;">Suhl</h5>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="blucher_6" class="custom-control-input">
            <label class="custom-control-label" for="blucher_6">Blücher 6</label>
          </div>
          <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" id="puschkin_1" class="custom-control-input">
            <label class="custom-control-label" for="puschkin_1">Puschkin 1</label>
          </div>
          <h5 class="lead" style="color: #007bff;">Leipzig</h5>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="landsberger_4" class="custom-control-input">
            <label class="custom-control-label" for="landsberger_4">Landsberger 4</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="landsberger_23" class="custom-control-input">
            <label class="custom-control-label" for="landsberger_23">Landsberger 23</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="mehring_3" class="custom-control-input">
            <label class="custom-control-label" for="mehring_3">Mehring 3</label>
          </div>
        </div> <!-- col-2 -->
        <div class="col-3">
          <h5 class="lead" style="color: #007bff;">Chemnitz</h5>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="barbarossa_2" class="custom-control-input">
            <label class="custom-control-label" for="barbarossa_2">Barbarossa 2</label>
          </div>
          <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" id="park_28" class="custom-control-input">
            <label class="custom-control-label" for="park_28">Park 28</label>
          </div>
          <h5 class="lead" style="color: #007bff;">Berlin</h5>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="trachenberg_93" class="custom-control-input">
            <label class="custom-control-label" for="trachenberg_93">Trachenberg 93</label>
          </div>
        </div> <!-- col-2 -->
        <div class="col-3">
          <h5 class="lead" style="color: #007bff;">Dresden</h5>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="glashutter_101" class="custom-control-input">
            <label class="custom-control-label" for="glashutter_101">glashütter 101</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="glashutter_101A" class="custom-control-input">
            <label class="custom-control-label" for="glashutter_101A">glashütter 101A</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" id="loscher_16" class="custom-control-input">
            <label class="custom-control-label" for="loscher_16">löscher_16</label>
          </div>
          <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" id="menelssohn_18" class="custom-control-input">
            <label class="custom-control-label" for="menelssohn_18">Menelssohn 18</label>
          </div>
        </div> <!-- col-2 -->
     
      </div> <!-- /.row -->



      </div> <!-- /.container -->
      </div> <!--body -->
      <div class="modal-footer modal-footer-fixed">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>