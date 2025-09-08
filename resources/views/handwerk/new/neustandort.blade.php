@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header',['title'=>'Neu Standort'])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
        <div class="card card-secondary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store_handwerk')}}" method="post">
              @csrf
              <!-- child cards -->
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('handwerk.layout.submitter')
                <!--end Submitter Section -->
                <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-secondary card-outline">
                    <div id="underform">
                     <input type="hidden" name="problem_type" value="Neustandort">
                      <div class="card-body box-profile form-group">
                        <div class="row">
                          <fieldset class="border rounded px-2 mb-2 col-lg-12">
                            <legend class="w-auto"><i class="fas fa-map-marker-alt" style="color: #661421;"></i></legend>
                            <div class="row">
                              <div class="form-group col-lg-6">
                                <label for="printer_place"> Standort &nbsp;<i class="fa-solid fa-hammer fa-lg" style="color: #004873;"></i></label>
                                <select class="custom-select form-control mb-2" id="printer_place" name="location_id"
                                  required>
                                </select>
                              </div>
                              <div class="form-group col-lg-6">
                                <label for="custom_room"> Raum / Ort &nbsp;<i class="fa-solid fa-hammer fa-lg" style="color: #004873;"></i></label>
                                <input type="text" name="custom_room" class="form-control" required>
                              </div>
                            </div>
                            <div>
                            <p class="text-sm text-gray text-bold"><i class="fa-solid fa-circle-info fa-lg"></i> Falls Stadt oder Adresse nicht im Dropdown-Menü vorhanden sind, kontaktieren Sie bitte die IT-Abteilung.</p>
                            </div>
                          </fieldset>
                        </div>
                        <div class="form-group mt-4">
                          <label for="notizen"> Anforderung</label>
                          <textarea type="text" name="notizen" class="form-control notizen"></textarea>
                        </div>
                        <div>
                          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
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
  </div><!-- /.container-fluid -->
</section><!-- /.content -->
@endsection

@section('script')
<script>
  function resetDropdown(id, placeholder) {
    $(`#${id}`).empty().append(new Option(placeholder, ''));
  }

  $(document).ready(function () {
    let selectAddresslisten = [];
    let roomlisten = [];

    resetDropdown('printer_place', "Standort...");
    resetDropdown('printer_room', "Raum...");

    $.ajax({
      type: "get",
      url: "{{route('room_list')}}",
    }).done(function (data) {
      $("body #printer_place").append('<optgroup label="' + data.place.pnname + '" id="' + data.place.id + '" ></optgroup>');

      data['locations'].map((item) => {
        $(`#printer_place #${data.place.id}`).append(new Option(item.address, item.id));
        selectAddresslisten.push(item);
      });
    });

    $(document).on("change", "#printer_place", function () {
      resetDropdown('printer_room', "Raum...");

      selectAddresslisten.map((address) => {
        if (address.id == $(this).val()) {
          address.invrooms.map((room) => {
            $("#printer_room").append(new Option(room.rname + ' (' + room.altrname + ')', room.id));
            roomlisten.push(room);
          });
        }
      });
    });
    $('#submitter_standort_exception').on('change', function() {
    var newCity = $(this).val();
    var url = "{{ route('room_list') }}/" + newCity;

    $.ajax({
        type: "get",
        url: url,
    }).done(function (data) {
        // Empty the current dropdowns
        $("#printer_place").empty();
        $("#printer_room").empty();

        // Fill the printer_place dropdown with new data
        $("body #printer_place").append('<optgroup label="' + data.place.pnname + '" id="' + data.place.id + '" ></optgroup>');
        
        data['locations'].map((item) => {
            $(`#printer_place #${data.place.id}`).append(new Option(item.address, item.id));
            selectAddresslisten.push(item);
        });
    });
});
  });
</script>
@endsection