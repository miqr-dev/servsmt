@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header',['title'=>'PC Standort ändern'])

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
                      <input type="hidden" name="problem_type" value="Anderer PC Standort">
                      <div class="card-body box-profile form-group">
                        <div class="row col-md-12">
                          <div class="form-group col-md-6">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto">Aktueller Standort &nbsp;<i class="fas fa-desktop" style="color:#e3342f;"></i></legend>
                              <label for="pc_current_place">PC Standort &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="pc_current_place" name="pc_current_place" required></select>

                              <label for="pc_current_room">Raum &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="pc_current_room" name="pc_current_room" required></select>

                              <label for="pc_ids">PCs & Laptops &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="pc_ids" name="pc_ids[]" multiple required></select>
                            </fieldset>
                          </div>

                          <div class="form-group col-md-6">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto">Neuer Standort &nbsp;<i class="fas fa-desktop" style="color:#285D17;"></i></legend>
                              <label for="pc_target_place">PC Standort &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="pc_target_place" name="tel_target_place" required></select>

                              <label for="pc_target_room">Raum &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="pc_target_room" name="tel_target_room" required></select>
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

    $('#pc_ids').select2({
      placeholder: 'PCs auswählen...'
    });

    $('#pc_current_place').find('option').remove();
    $('#pc_current_place').find('optgroup').remove();
    $('#pc_current_place').append(new Option('Standort...', ''));

    $('#pc_current_room').find('option').remove();
    $('#pc_current_room').append(new Option('Raum...', ''));

    $.ajax({
      type: 'get',
      url: "{{route('item.listen')}}",
    }).done(function(data) {
      currentAddressList = [];
      $.each(data['places'], function(index, item) {
        $('body #pc_current_place').append('<optgroup label="' + index + '" id="' + item + '"></optgroup>');
      });

      $.each(data['locations'], function(index, item) {
        $('#pc_current_place #' + item.place_id).append(new Option(item.address, item.id));
        currentAddressList.push(item);
      });
    });

    $(document).on('change', '#pc_current_place', function() {
      $('#pc_ids').empty().trigger('change');
      $('#pc_current_room').find('option').remove();
      $('#pc_current_room').append(new Option('Raum...', ''));

      for (let i = 0; i < currentAddressList.length; i++) {
        if (currentAddressList[i].id == $(this).val()) {
          $.each(currentAddressList[i].invrooms, function(index, item) {
            $('#pc_current_room').append(new Option(item.rname + ' (' + item.altrname + ')', item.id));
          });
        }
      }
    });

    $(document).on('change', '#pc_current_room', function() {
      $.ajax({
        type: 'post',
        url: "{{ route('pc_in_room') }}",
        data: {
          pcs: $(this).val()
        },
        success: function(resp) {
          $('#pc_ids').empty();
          $.each(resp, function(index, item) {
            const label = item.gname + (item.invnr ? ' (' + item.invnr + ')' : '');
            $('#pc_ids').append(new Option(label, item.id, false, false));
          });
          $('#pc_ids').trigger('change');
        },
        error: function() {
          alert('Error');
        }
      });
    });

    let targetAddressList = [];
    $('#pc_target_place').find('option').remove();
    $('#pc_target_place').find('optgroup').remove();
    $('#pc_target_place').append(new Option('Standort...', ''));

    $('#pc_target_room').find('option').remove();
    $('#pc_target_room').append(new Option('Raum...', ''));

    $.ajax({
      type: 'get',
      url: "{{route('item.listen')}}",
    }).done(function(data) {
      targetAddressList = [];
      $.each(data['places'], function(index, item) {
        $('body #pc_target_place').append('<optgroup label="' + index + '" id="' + item + '"></optgroup>');
      });

      $.each(data['locations'], function(index, item) {
        $('#pc_target_place #' + item.place_id).append(new Option(item.address, item.id));
        targetAddressList.push(item);
      });
    });

    $(document).on('change', '#pc_target_place', function() {
      $('#pc_target_room').find('option').remove();
      $('#pc_target_room').append(new Option('Raum...', ''));

      for (let i = 0; i < targetAddressList.length; i++) {
        if (targetAddressList[i].id == $(this).val()) {
          $.each(targetAddressList[i].invrooms, function(index, item) {
            $('#pc_target_room').append(new Option(item.rname + ' (' + item.altrname + ')', item.id));
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
