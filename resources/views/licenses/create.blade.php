@extends('layouts.admin_layout.admin_layout')

<style>

</style>
@section('content')

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
  <!-- End News -->
  <section class="content-header text-center">
    <div class="container fluid">
      <div class="row">
        <div class="col-12 mx-auto">
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- Main content -->
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
          <!-- child cards -->
            <div class="row mx-auto">
              <!-- first card -->
              <div class="col-lg-9">
                <div class="card card-primary card-outline">
                  <div class="position-relative">
                    <div class="card-body box-profile form-group">
                      <div class="row">
                        <div class="col-md-6">

                            <form action="{{ route('licenses.store') }}" method="POST">
                              @csrf
                              <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Lizenz</strong>
                                      <input type="text" name="name"  class="form-control">
                                  </div>
                                  <div class="form-group">
                                      <strong>Wo</strong>
                                      <input type="text" name="where"  class="form-control">
                                  </div>
                                  <div class="form-group">
                                      <strong>Version</strong>
                                      <input type="text" name="version" class="form-control">
                                  </div>
                                  <div class="form-group">
                                      <strong>Gültig bis</strong>
                                      <input type="text" class="form-control valid" name="valid" autocomplete="off" >
                                  </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Bemerkung</strong>
                                      <textarea class="form-control" style="height:150px" name="comment"></textarea>
                                  </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                  <button type="submit" class="btn btn-primary">Speichern</button>
                                </div>
                              </div>
                          </form>
  
                        </div>
                      </div>
                    <!-- /.card-body -->
                  </div>
                </div>
              </div>
            </div>
              <!--end first card -->
              <!-- second card -->
          <div class="col-lg-3">
            <!-- Card  -->
            <div class="card card-primary card-outline" style="background-color: #661421;">
              <div class="card-body">
                <h5 class="card-title mb-3" style="color:#fff;"><strong>Shortcut-Panel</strong></h5>
                <div class="card-text">
                  <div class="list-group">
                    <a href="{{route ('profile')}}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="far fa-user fa-lg"></i><strong> Eigenes Profil bearbeiten</strong></a>
                    <a href="{{ url('/contacts') }}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="far fa-address-book fa-lg"></i><strong> Adressbuch</strong></a>
                    <a href="{{ route('ticket.index') }}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-ticket-alt fa-lg"></i><strong> Ticket erstellen</strong>
                    <a href="{{ route('ticket.usertickets') }}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-clipboard-list fa-lg"></i><strong>     Meine Tickets</strong>
                    </a>
                  </div><!-- End Linst Group -->
                </div><!-- End Card Text -->
              </div><!-- End Card Body -->
            </div> <!-- End Card  -->
          </div><!-- End First Section -->
              <!--end second card -->
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
  <!-- /.content -->
<!-- /.content-wrapper -->
  </div>
</section>

@endsection

@section('script')

<script>



$(function() {
$.datepicker.setDefaults($.datepicker.regional["de"]);

  $('.valid').daterangepicker({
      autoUpdateInput: false,
      singleDatePicker: true,
      locale: {
          cancelLabel: 'Clear'
      }
  });

  $('.valid').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('MM/DD/YYYY'));
  });

  $('.valid').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
  });

});

</script>

@endsection



