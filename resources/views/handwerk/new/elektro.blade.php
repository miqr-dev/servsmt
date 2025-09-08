@extends('layouts.admin_layout.admin_layout')

<style>
  /* Styling for custom checkbox */
  .form-check-input[type="checkbox"] {
    opacity: 0;
    position: absolute;
    margin: 0;
    z-index: -1;
    width: 0;
    height: 0;
    overflow: hidden;
    left: 0;
    pointer-events: none;
  }

  .form-check-input[type="checkbox"]+.form-check-label {
    position: relative;
    cursor: pointer;
    padding: 0;
  }

  .form-check-input[type="checkbox"]+.form-check-label:before {
    content: '';
    margin-right: 10px;
    display: inline-block;
    vertical-align: text-top;
    width: 20px;
    height: 20px;
    background: white;
    border: 1px solid #004873;
    border-radius: 4px;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
  }

  .form-check-input[type="checkbox"]:checked+.form-check-label:before {
    background: #004873;
    border-color: #004873;
  }

  .form-check-input[type="checkbox"]:checked+.form-check-label:after {
    content: '';
    position: absolute;
    left: 6px;
    top: 12px;
    background: white;
    width: 2px;
    height: 2px;
    box-shadow: 2px 0 0 white, 4px 0 0 white, 4px -2px 0 white, 4px -4px 0 white, 4px -6px 0 white, 4px -8px 0 white;
    transform: rotate(45deg);
  }

  .highlighted {
    padding: 10px;
    margin-left: 5px;
    border-radius: 5px;
    border: 1px solid #ced4da;
    /* Change this to the color you want for the border */
    background-color: #f9f9f9;
    /* A light grey background for selected items */
  }
</style>

@section('content')
@include('tickets.layout_ticket.header',['title'=>'Elektro'])

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
                      <input type="hidden" name="problem_type" value="Mobiliar - Elektro">
                      <div class="card-body box-profile form-group">
                            <div class="row">
                              <div class="form-group col-lg-6">
                                <label for="printer_place"> Standort &nbsp;<i class="fa-solid fa-hammer fa-lg"
                                    style="color: #004873;"></i></label>
                                <select class="custom-select form-control mb-2" id="printer_place" name="location_id"
                                  required>
                                </select>
                              </div>
                              <div class="form-group col-lg-6">
                                <label for="printer_room"> Raum &nbsp;<i class="fa-solid fa-hammer fa-lg"
                                    style="color: #004873;"></i></label>
                                <select class="custom-select form-control mb-2" id="printer_room" name="room_id"
                                  >
                                </select>
                                <div>
                                  <p class="text-sm text-gray text-bold"><i class="fa-solid fa-circle-info fa-lg"></i>
                                    Falls
                                    der Raum nicht im Dropdown-Menü enthalten ist, verwenden Sie die
                                    benutzerdefinierte Eingabe unten.</p>
                                </div>
                              </div>
                              <div class="form-group col-lg-6">
                              </div>
                              <div class="form-group col-lg-6">
                                <label for="custom_room"> Raum / Ort</label>
                                <input type="text" name="custom_room" class="form-control">
                              </div>
                            </div>
                        <div class="row my-4">
                          <div class="col-lg-12">
                            <div class="d-flex justify-content-start flex-wrap card-deck">
                              <div class="form-check d-flex align-items-center">
                                <div class="flex-grow-1">
                                  <input class="form-check-input item-checkbox" type="checkbox" name="kühlschrank"
                                    data-has-quantity-input="true" id="kühlschrank">
                                  <label class="form-check-label" for="kühlschrank">Kühlschrank</label>
                                </div>
                                <div class="flex-shrink-0">
                                  <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                    name="kühlschrank_qty" min="0" style="width: 60px; display: none;">
                                </div>
                              </div>
                              <div class="form-check d-flex align-items-center">
                                <div class="flex-grow-1">
                                  <input class="form-check-input item-checkbox" type="checkbox" name="geschirrspüler"
                                    data-has-quantity-input="true" id="geschirrspüler">
                                  <label class="form-check-label" for="geschirrspüler">Geschirrspüler</label>
                                </div>
                                <div class="flex-shrink-0">
                                  <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                    name="geschirrspüler_qty" min="0" style="width: 60px; display: none;">
                                </div>
                              </div>
                              <div class="form-check d-flex align-items-center">
                                <div class="flex-grow-1">
                                  <input class="form-check-input item-checkbox" type="checkbox" name="kaffeemaschine"
                                    data-has-quantity-input="true" id="kaffeemaschine">
                                  <label class="form-check-label" for="kaffeemaschine">Kaffeemaschine</label>
                                </div>
                                <div class="flex-shrink-0">
                                  <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                    name="kaffeemaschine_qty" min="0" style="width: 60px; display: none;">
                                </div>
                              </div>
                              <div class="form-check d-flex align-items-center">
                                <div class="flex-grow-1">
                                  <input class="form-check-input item-checkbox" type="checkbox" name="ventilator"
                                    data-has-quantity-input="true" id="ventilator">
                                  <label class="form-check-label" for="ventilator">Ventilator</label>
                                </div>
                                <div class="flex-shrink-0">
                                  <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                    name="ventilator_qty" min="0" style="width: 60px; display: none;">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="notizen"> Notizen</label>
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

    // Checkbox change event
    $('.item-checkbox').change(function () {
      const isChecked = $(this).is(':checked');
      const formCheckElement = $(this).closest('.form-check');
      const quantityInput = formCheckElement.find('.quantity-input');

      // If checkbox is checked, show quantity input
      // If checkbox is unchecked, hide quantity input
      quantityInput.toggle(isChecked).val('1');

      // Reset quantity input value when checkbox is unchecked
      if (!isChecked) {
        quantityInput.val('');
      }

      // If checkbox is checked, add highlighted class to parent
      // If checkbox is unchecked, remove highlighted class from parent
      formCheckElement.toggleClass('highlighted', isChecked);
    });

    // Prevent manual input of zero or negative values
    $('.quantity-input').on('input', function () {
      if ($(this).val() < 1) {
        $(this).val('1');
      }
    });


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