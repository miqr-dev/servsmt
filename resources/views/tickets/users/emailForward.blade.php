@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Email Weiterleiten'])
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
                    <div class="card-body box-profile form-group">
                    </div>
                    <div id="underform">
                      <!-- ! Jquery forms here -->
                      <input type="hidden" name="problem_type" value="Email Weiterleitung">
                      <div class="card-body box-profile form-group">
                        <div class="row col-md-12">
                          <div class="form-group col-md-12">
                            <div class="row col-md-12">
                              <div class="form-group col-md-6">
                                <label for="forward_on">Weiterleitung an: &nbsp;<i class="fas fa-feather-alt fa-lg"
                                    style="color: #661421;"></i>
                                    &nbsp;<i class="fa-solid fa-right-to-bracket fa-lg" style="color:green;"></i></label>
                                <input type="text" class="form-control" name="forward_on" required>
                              </div>
                              <div class="form-group col-md-6">
                                <label for="forward_on">Weiterleitung von: &nbsp;<i class="fas fa-feather-alt fa-lg"
                                    style="color: #661421;"></i>
                                    &nbsp;<i class="fa-solid fa-right-from-bracket fa-lg" style="color:red;"></i></label>
                                <input type="text" class="form-control" name="forward_from" required>
                              </div>
                              <div class="form-group mb-2 col-md-6">
                                <label for="forward_required_at">Benötigt ab</label>
                                <input type="text" class="form-control startdate" name="forward_required_at" required>
                              </div>
                            </div>
                            <div class="col-md-12">
                              <div class="custom-control custom-checkbox mb-4" style="color: red;">
                                <input type="checkbox" class="custom-control-input" id="cancelForward"
                                  name="cancelForward">
                                <label class="custom-control-label" for="cancelForward">Weiterleitung abbrechen </label>
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


  $(document).ready(function () {


    $(function () {
      moment.locale('de');
      $('.startdate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        startDate: moment(),
        minDate: moment(),
        minYear: parseInt(moment().format('YYYY')) - 1,
        maxYear: parseInt(moment().format('YYYY')) + 1,
        opens: 'center',
        locale: {
          format: 'DD-MM-YYYY'
        }
      });

    });
    $('.notizen').summernote({
      height: 150,
      lang: 'de-DE'
    });

  });


</script>
@endsection