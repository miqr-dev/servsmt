@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Probleme'])
 <!-- Main content -->
  <section class="content">
    <div class="container-fluid col-lg-12">
      <div class="row">
        <div class="col-12 mx-auto">
          <div class="card card-primary card-outline">
            <div class="card-body box-profile form-group">
              <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
                @csrf
                <input type="hidden" name="problem_type" value="Probleme">
                <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                  <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                      <div class="card-body box-profile form-group">
                        <div class="row mx-auto">
                          <!-- Allgemein -->
                          <div class="col-lg-4">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto">Allgemein <i class="fas fa-desktop"></i></legend>
                                <ul class="list-unstyled">
                                  <li>
                                    <div class="custom-control custom-checkbox mb-2">
                                      <input type="checkbox" class="custom-control-input" id="geht_nicht_an" name="geht_nicht_an">
                                      <label class="custom-control-label" for="geht_nicht_an">Geht nicht an</label>
                                    </div>
                                  </li>
                                  <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="blue" name="blue">
                                    <label class="custom-control-label" for="blue">Geht an / Blue Screen</label>
                                  </div>
                                  </li>
                                  <li>
                                    <div class="custom-control custom-checkbox mb-2">
                                      <input type="checkbox" class="custom-control-input" id="black" name="black">
                                      <label class="custom-control-label" for="black">Geht an / Black Screen</label>
                                    </div>
                                  </li>
                                  <li>
                                    <div class="custom-control custom-checkbox mb-2">
                                      <input type="checkbox" class="custom-control-input" id="slow_computer" name="slow_computer">
                                      <label class="custom-control-label" for="slow_computer">Sehr Langsam</label>
                                    </div>
                                  </li>
                                </ul>
                            </fieldset>
                          </div>
                          <!-- Peripherie-->
                          <div class="col-lg-4">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto"> Peripherie <i class="fas fa-mouse"></i></legend>
                              <ul class="list-unstyled">
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="web_cam_problem" name="web_cam_problem">
                                    <label class="custom-control-label" for="web_cam_problem">Webcam funktioniert nicht </label>
                                  </div>
                                </li>
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="head_set_problem" name="head_set_problem">
                                    <label class="custom-control-label" for="head_set_problem">Headset funktioniert nicht</label>
                                  </div>
                                </li>
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="lautsprecher_mal" name="lautsprecher_mal">
                                    <label class="custom-control-label" for="lautsprecher_mal">Lautsprecher funktioniert nicht</label>
                                  </div>
                                </li>
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="keyboard_malfunction" name="keyboard_malfunction">
                                    <label class="custom-control-label" for="keyboard_malfunction">Tastatur funktioniert nicht</label>
                                  </div>
                                </li>
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="mouse_mal" name="mouse_mal">
                                    <label class="custom-control-label" for="mouse_mal">Maus funktioniert nicht</label>
                                  </div>
                                </li>
                              </ul>
                            </fieldset>
                          </div>
                          <!-- Sonstiges  -->
                          <div class="col-lg-4">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto"> Sonstiges <i class="fas fa-heartbeat"></i></legend>
                              <ul class="list-unstyled">
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="slow_network" name="slow_network">
                                    <label class="custom-control-label" for="slow_network">Netzwerkzugriff langsam</label>
                                  </div>
                                </li>
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="no_network_drive" name="no_network_drive">
                                    <label class="custom-control-label" for="no_network_drive">Keine Netzlaufwerke</label>
                                  </div>
                                </li>
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="laud_fan" name="laud_fan">
                                    <label class="custom-control-label" for="laud_fan">lautes Lüftergeräusch</label>
                                  </div>
                                </li>
                                <li>
                                  <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="other" name="other">
                                    <label class="custom-control-label" for="other">Sonstiges</label>
                                  </div>
                                </li>
                              </ul>
                            </fieldset>
                          </div>
                        </div>
                      </div>    
                      <div class="card-body box-profile form-group">       
                      <div class="row col-md-12">
                        <div class="form-group col-md-6">
                          <label for="searchcomputer"> Welcher Rechner &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                          <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer" required>
                            <option class="form-control" value="">Bitte Wählen</option>
                            @foreach($computers as $computer)
                              <option class="form-control" value="{{$computer['id']}}">{{$computer['gname']}}</option>
                            @endforeach
                          </select>
                        </div>
                        @include('tickets.layout_ticket.note',['discription'=>'Beschreibung'])
                        </div>                  
                        <div>
                          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                        </div>
                      </div>
                    </div>
                  </div><!--end second card -->
                </div>
              </form>
            </div> <!-- /.card-body -->
          </div><!-- /.card -->
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </section><!-- /.content -->
@endsection
@section('script')
<script>
  $.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});

</script>
@endsection





