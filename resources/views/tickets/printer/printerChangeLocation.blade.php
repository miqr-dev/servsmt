@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header',['title'=>'Drucker Standort ändern'])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
              @csrf
              <div class="row mx-auto">
                @include('tickets.layout_ticket.submitter')
                <div class="col-lg-8">
                  <div class="card card-primary card-outline">
                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Anderer Druckerstandort">
                      <div class="card-body box-profile form-group">
                        <div class="row col-md-12">
                          <div class="form-group col-md-6">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto">Aktueller Standort &nbsp;<i class="fas fa-print" style="color:#e3342f;"></i></legend>
                              <label for="printer_current_place">Druckerstandort &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="printer_current_place" name="printer_current_place" required></select>

                              <label for="printer_current_room">Raum &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="printer_current_room" name="printer_current_room" required></select>

                              <label for="printer_name">Drucker &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="printer_name" name="searchcomputer" required></select>
                            </fieldset>
                          </div>

                          <div class="form-group col-md-6">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto">Neuer Standort &nbsp;<i class="fas fa-print" style="color:#285D17;"></i></legend>
                              <label for="printer_target_place">Druckerstandort &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="printer_target_place" name="tel_target_place" required></select>

                              <label for="printer_target_room">Raum &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="printer_target_room" name="tel_target_room" required></select>
                            </fieldset>
                          </div>

                          <div class="form-group col-md-6 col-lg-12">
                            <label for="notizen">Notizen</label>
                            <textarea type="text" name="notizen" class="form-control notizen"></textarea>
                          </div>
                        </div>

                        <div>
                          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('script')
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $(document).ready(function() {
    let currentAddressList = [];

    $('#printer_current_place').find('option').remove();
    $('#printer_current_place').find('optgroup').remove();
    $('#printer_current_place').append(new Option('Standort...', ''));

    $('#printer_current_room').find('option').remove();
    $('#printer_current_room').append(new Option('Raum...', ''));

    $.ajax({
      type: 'get',
      url: "{{route('item.listen')}}",
    }).done(function(data) {
      currentAddressList = [];
      $.each(data['places'], function(index, item) {
        $('body #printer_current_place').append('<optgroup label="' + index + '" id="' + item + '"></optgroup>');
      });

      $.each(data['locations'], function(index, item) {
        $('#printer_current_place #' + item.place_id).append(new Option(item.address, item.id));
        currentAddressList.push(item);
      });
    });

    $(document).on('change', '#printer_current_place', function() {
      $('#printer_name').find('option').remove();
      $('#printer_current_room').find('option').remove();
      $('#printer_current_room').append(new Option('Raum...', ''));

      for (let i = 0; i < currentAddressList.length; i++) {
        if (currentAddressList[i].id == $(this).val()) {
          $.each(currentAddressList[i].invrooms, function(index, item) {
            $('#printer_current_room').append(new Option(item.rname + ' (' + item.altrname + ')', item.id));
          });
        }
      }
    });

    $(document).on('change', '#printer_current_room', function() {
      $.ajax({
        type: 'post',
        url: "{{ route('printer_in_room') }}",
        data: {
          printers: $(this).val()
        },
        success: function(resp) {
          $('#printer_name').find('option').remove();
          $.each(resp, function(index, item) {
            $('#printer_name').append(new Option(item.gname, item.id));
          });
        },
        error: function() {
          alert('Error');
        }
      });
    });

    let targetAddressList = [];
    $('#printer_target_place').find('option').remove();
    $('#printer_target_place').find('optgroup').remove();
    $('#printer_target_place').append(new Option('Standort...', ''));

    $('#printer_target_room').find('option').remove();
    $('#printer_target_room').append(new Option('Raum...', ''));

    $.ajax({
      type: 'get',
      url: "{{route('item.listen')}}",
    }).done(function(data) {
      targetAddressList = [];
      $.each(data['places'], function(index, item) {
        $('body #printer_target_place').append('<optgroup label="' + index + '" id="' + item + '"></optgroup>');
      });

      $.each(data['locations'], function(index, item) {
        $('#printer_target_place #' + item.place_id).append(new Option(item.address, item.id));
        targetAddressList.push(item);
      });
    });

    $(document).on('change', '#printer_target_place', function() {
      $('#printer_target_room').find('option').remove();
      $('#printer_target_room').append(new Option('Raum...', ''));

      for (let i = 0; i < targetAddressList.length; i++) {
        if (targetAddressList[i].id == $(this).val()) {
          $.each(targetAddressList[i].invrooms, function(index, item) {
            $('#printer_target_room').append(new Option(item.rname + ' (' + item.altrname + ')', item.id));
          });
        }
      }
    });

    $('.notizen').summernote({
      height: 150,
      lang: 'de-DE'
    });
  });
</script>
@endsection
