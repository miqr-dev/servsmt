@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Terminal_TN'])
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
                      <!-- ! Jquery forms here --> 
                      <input type="hidden" name="problem_type" value="Terminal TN Benutzer">
                      <div class="card-body box-profile form-group">       
                        <div class="row col-md-12">
                          <div class="form-group col-md-6">
                            <div class="row">
                              <div class="form-group col-md-12">
                                <label for="terminal_name">Benutzername des Teilnehmer &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                                <input type="text" class="form-control" name="terminal_name" required>
                              </div>
                              <div class="form-group col-md-12">
                                <label for="terminal_expiry">Maßnahmeende &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                                 <input type="text" class="form-control terminal_expiry" name="terminal_expiry" required>
                              </div>
                                <div class="col-md-12 d-flex justify-content-around">
                                  <div class="custom-control custom-checkbox mb-4">
                                    <input type="checkbox" class="custom-control-input terminal_datev" id="terminal_datev" name="terminal_datev">
                                    <label class="custom-control-label" for="terminal_datev">Datev</label>
                                  </div>
                                  <div class="custom-control custom-checkbox mb-4">
                                    <input type="checkbox" class="custom-control-input" id="terminal_lexware" name="terminal_lexware">
                                    <label class="custom-control-label" for="terminal_lexware">Lexware</label>
                                  </div>
                                </div>
                            </div>
                          </div>
                          <div class="form-group col-md-6">
                          </div> 
                          <div class="form-group col-md-6 col-lg-12">
                            <label for="notizen">Beschreibung</label>
                            <textarea type="text" name="notizen" class="form-contro notizen" ></textarea>
                          </div>
                          </div>                  
                          <div>
                            <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                          </div>
                        </div>
                      <!-- ! Jquery forms ends here -->
                    </div>
                  </div>
                </div><!--end second card -->
              </div>
            </form>
            </div><!-- /.card-body -->
          </div><!-- /.card -->
        </div> <!-- /.col -->
      </div> <!-- /.row -->
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

$(function() {
  moment.locale('de');
  $('.terminal_expiry').daterangepicker({
		singleDatePicker: true,
		showDropdowns: true,
    startDate: moment(),
    minDate: moment(),
		minYear: parseInt(moment().format('YYYY'))-1,
		maxYear: parseInt(moment().format('YYYY'))+1,
    opens: 'center',
		locale: {
			format: 'DD-MM-YYYY'
		}
  });

});


</script>
@endsection





