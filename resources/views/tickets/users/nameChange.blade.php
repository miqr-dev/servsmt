@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Benutzer - Namensänderung'])
<!-- Main content -->
  <section class="content">
    <div class="container-fluid col-lg-12">
      <div class="row">
        <div class="col-12 mx-auto">
          <!-- Profile Image -->
          <div class="card card-primary card-outline">
            <div class="card-body box-profile form-group">
              <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
                @csrf
                <!-- child cards -->
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                  <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-primary card-outline">
                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Anmelde Probleme">
                      <div class="card-body box-profile form-group">       
                        <div class="row col-md-12">
                          <div class="form-group col-md-12">
                            <div class="row col-md-12">
                              <div class="form-group col-md-6">
                                <label for="submitter">Vollständiger Name &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                                <input type="text" class="form-control" name="password_name" required>
                                <input type="hidden" name="expiring_date" id="expiring_date" value="">
                                </div>
                              <div class="form-group col-md-6">
                                  <label for="searchcomputer"> Welcher Rechner </label>
                                  <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer">
                                  <option class="form-control" value="">Bitte Wählen</option>
                                  @foreach($computers as $computer)
                                    <option class="form-control" value="{{$computer['id']}}">{{$computer['gname']}}</option>
                                  @endforeach
                                  </select>
                                </div>
                              </div>
                                <div class="col-md-12 d-flex justify-content-around">
                                  <div class="custom-control custom-checkbox mb-4">
                                    <input type="checkbox" class="custom-control-input abgelaufen" id="abgelaufen" name="abgelaufen">
                                    <label class="custom-control-label" for="abgelaufen">Konto Abgelaufen </label>
                                  </div>
                                  <div class="custom-control custom-checkbox mb-4">
                                    <input type="checkbox" class="custom-control-input inaktiv" id="inaktiv" name="inaktiv">
                                    <label class="custom-control-label" for="inaktiv">Inaktives Konto</label>
                                  </div>
                                  <div class="custom-control custom-checkbox mb-4">
                                    <input type="checkbox" class="custom-control-input" id="forgotten" name="forgotten">
                                    <label class="custom-control-label" for="forgotten">Passwort vergessen</label>
                                  </div>
                                  <div class="custom-control custom-checkbox mb-4">
                                    <input type="checkbox" class="custom-control-input" id="other_error_participant" name="other_error_participant">
                                    <label class="custom-control-label" for="other_error_participant">Anderer Fehler</label>
                                  </div>
                                </div>
                            </div>
                          </div>
                          <div class="form-group col-md-6">
                          </div> 
                          <div class="form-group col-md-6 col-lg-12">
                            <label for="notizen">Beschreibung</label>
                            <textarea type="text" name="notizen" class="form-contro notizen"></textarea>
                          </div>
                          <div>
                            <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                          </div>
                      </div>
                      </div> 
                    </div>
                  </div>
                </div><!--end second card -->
              </div>
            </form>
            </div><!-- /.card-body -->
          </div><!-- /.card -->
        </div> <!-- /.col -->
      </div> <!-- /.row -->
  </section><!-- /.content -->



@endsection

@section('script')

@endsection





