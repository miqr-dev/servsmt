@extends('layouts.admin_layout.admin_layout')

<style>
  .button-group {
    margin-right: 20px;
  }

  .item-selected {
    border: 1px solid #ced4da;
    /* Change this to the color you want for the border */
    padding: 10px;
    /* Adjust this to the amount of padding you want */
    border-radius: 5px;
    /* Added to make the border rounded */
    background-color: #f9f9f9;
    /* A light grey background for selected items */
  }

  .form-check-input {
    margin-top: 0.3em;
    margin-right: 0.3em;
  }

  .form-check-label {
    margin-right: 1em;
  }

  .form-check {
    margin-bottom: 0.5em;
  }

  /* Styling for custom checkbox */
  .form-check-input[type="checkbox"] {
    opacity: 0;
    position: absolute;
    margin: 0;
    z-index: 1;
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
    border: 1px solid #65A30D;
    border-radius: 4px;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
  }

  .form-check-input[type="checkbox"]:checked+.form-check-label:before {
    background: #65A30D;
    border-color: #65A30D;
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
</style>

@section('content')
@include('tickets.layout_ticket.header', [
'title' => 'Printmarketing',
'colorClass' => 'ticket_header_korso',
'buttonClass' => 'btn-outline-green' // New class for button color
])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
        <div class="card card-thirdary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store_korso')}}" method="post" enctype="multipart/form-data">
              @csrf
              <!-- child cards -->
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('korso.layout.submitter')
                <!--end Submitter Section -->
                <!-- second card -->
                <div class="col-lg-10">
                  <div class="card card-thirdary card-outline">
                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Printmarketing">
                      <div class="card-body box-profile form-group">
                        <div class="row">
                          <div class="form-group col-lg-4">
                            <label for="printer_place"> Standort &nbsp;<i class="fa-solid fa-thumbtack fa-lg"
                                style="color: #65A30D;"></i></label>
                            <select class="custom-select form-control mb-2" id="printer_place" name="location_id"
                              required>
                            </select>
                          </div>
                        </div>
                        <div class="row my-4 p-2">
                          <div class="col-lg-12">
                            <ul class="nav nav-tabs mb-3" id="productTab" role="tablist">
                              <!-- <li class="nav-item">
                                <a class="nav-link" id="flyer-tab" data-toggle="tab" href="#flyer-options"
                                  role="tab">Flyer / Infomaterial</a>
                              </li> -->
                              <li class="nav-item">
                                <a class="nav-link active" id="flyer-info-tab" data-toggle="tab" href="#flyer-info-options"
                                  role="tab">Flyer / Infomaterial</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" id="giveaways-tab" data-toggle="tab" href="#giveaways-options"
                                  role="tab">Give Aways</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" id="stationery-tab" data-toggle="tab" href="#stationery-options"
                                  role="tab">Geschäftsausstattung</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" id="signage-tab" data-toggle="tab" href="#signage-options"
                                  role="tab">Beschilderung / Gestaltung</a>
                              </li>
                              <li class="nav-item">
                                <a class="nav-link" id="messe-tab" data-toggle="tab" href="#messe-options"
                                  role="tab">Messe & Sonstiges</a>
                              </li>
                            </ul>

                            <div class="tab-content" id="productTabContent">
                              <!-- <div class="tab-pane fade show" id="flyer-options" role="tabpanel">
                                @foreach($payers as $payer)
                                <div class="mb-3">
                                  <h5>{{ $payer->name }}</h5>
                                   <div class="ml-3">
                                    @foreach($payer->kcourses as $index => $kcourse)
                                    <div class="form-check d-flex align-items-center">
                                      <div class="flex-grow-1">
                                        <input class="form-check-input item-checkbox" type="checkbox"
                                          name="{{ $kcourse->name }}" data-has-quantity-input="true"
                                          id="kcourse_{{ $payer->id }}_{{ $index }}">
                                        <label class="form-check-label" for="kcourse_{{ $payer->id }}_{{ $index }}">
                                          {{ $kcourse->name }}
                                        </label>
                                      </div>
                                      <div class="flex-shrink-0">
                                        <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                          name="{{ $kcourse->name }}_qty" min="1" style="width: 60px; display: none;">
                                      </div>
                                    </div>
                                    @endforeach
                                  </div> 
                                </div>
                                @endforeach
                              </div> -->
                              
                              @include('korso.partials.flyer_info')
                              @include('korso.partials.give_aways')
                              @include('korso.partials.stationery')
                              @include('korso.partials.signage')
                              @include('korso.partials.fair_and_misc')

                            </div>

                          </div>
                        </div>

                        <div class="form-group">
                          <label for="notizen"> Notizen</label>
                          <textarea type="text" name="notizen" class="form-control summernote"></textarea>

                          <div class="form-group">
                            <label for="attachments" class="font-weight-bold">Anhänge <small class="text-muted">(Bilder
                                oder PDFs hochladen)</small></label>

                            <div class="custom-file">
                              <input type="file" name="attachments[]" id="attachments" class="custom-file-input"
                                multiple accept="image/*,application/pdf">
                              <label class="custom-file-label" for="attachments">Dateien auswählen...</label>
                            </div>

                            <small class="form-text text-muted mt-1">Maximale Größe: 5MB pro Datei</small>

                            <!-- Preview Area -->
                            <div id="file-preview" class="d-flex flex-wrap mt-3 border rounded p-2 bg-light"></div>
                          </div>
                        </div>

                        <!-- Preview Uploaded Files -->
                        <div id="file-preview" class="mt-3 d-flex flex-wrap"></div>
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


  function generateSelectOptions() {
    let options = '';
    for (let i = 0; i < 20; i++) {
      options += `<option value="${i}">${i}</option>`;
    }
    return options;
  }

  $(document).ready(function () {
    localStorage.setItem('sidebarState', 'collapsed');
    $('body').addClass('sidebar-collapse').removeClass('sidebar-open');

    // Completely disable the sidebar toggle button
    $('.nav-link[data-widget="pushmenu"]').off('click').on('click', function (e) {
      e.preventDefault(); // Prevent default action
      e.stopPropagation(); // Stop event from bubbling up
    });

    // Also block any JavaScript function that tries to toggle the sidebar
    if (typeof $.fn.PushMenu !== "undefined") {
      $.fn.PushMenu.prototype.toggle = function () {
        return false; // Disable toggle function
      };
    }

    $('form').submit(function (e) {
      let formData = new FormData(this);
      let selectedItems = [];

      // Handle normal items with checkboxes
      $('.item-checkbox:checked').each(function () {
        let itemName = $(this).attr('name');
        let quantityInput = $(this).closest('.form-check').find('.quantity-input');
        let quantity = quantityInput.length ? quantityInput.val() : 1;

        selectedItems.push({
          name: itemName,
          quantity: quantity ? parseInt(quantity) : 1
        });
      });

      // Handle special "Versandtasten Post" items (C4, C5, DL)
      let specialItems = ["C4", "C5", "DL", "C4_ohne", "C5_ohne", "DL_ohne"];
      specialItems.forEach(function (item) {
        let checkbox = $(`input[name="${item}"]`);
        let quantityInput = $(`input[name="${item}_qty"]`);

        if (checkbox.is(':checked') && quantityInput.length && quantityInput.val()) {
          selectedItems.push({
            name: item,
            quantity: parseInt(quantityInput.val())
          });
        }
      });

      // Remove old input if exists
      $('input[name="selected_items"]').remove();

      // Store as a hidden input field
      $('<input>').attr({
        type: 'hidden',
        name: 'selected_items',
        value: JSON.stringify(selectedItems)
      }).appendTo(this);
    });

    // Pre-populate select dropdowns with quantity options
    $('.quantity-select').html(generateSelectOptions());

    $('.item-checkbox').change(function () {
      var itemContainer = $(this).closest('.form-check');
      var qtyInput = itemContainer.find('.quantity-input');
      var dimensionList = itemContainer.find('.dimension-list');
      var heightAdjustableCheckbox = itemContainer.find('.height-check');
      var dimensionInput = itemContainer.find('.dimension-input');

      if ($(this).is(':checked')) {
        if ($(this).data('has-quantity-input')) {
          qtyInput.show();
          if (!qtyInput.val()) {
            qtyInput.val(qtyInput.attr('value') || 1); // use value attribute if defined, fallback to 1
          }
        }
        // NEW: Show Visitenkarten extras
        if ($(this).data('show-extra')) {
          $('.' + $(this).data('show-extra')).slideDown();
        }
        if ($(this).data('has-size-select')) {
          dimensionInput.show();
        }
        if ($(this).data('has-height-adjustable')) {
          heightAdjustableCheckbox.show();
        }
        dimensionList.show();
        itemContainer.addClass('item-selected');
      } else {
        qtyInput.hide().val('');
        dimensionInput.hide().val('');
        heightAdjustableCheckbox.hide().prop('checked', false);
        dimensionList.hide().find('.dimension-checkbox').prop('checked', false);
        dimensionList.find('.dimension-quantity').hide().val('');
        itemContainer.removeClass('item-selected');
        if ($(this).data('show-extra')) {
          $('.' + $(this).data('show-extra')).slideUp().find('input').val('');
        }
      }
    });

    $('.dimension-checkbox').change(function () {
      var listItem = $(this).closest('li');
      var qtyInput = listItem.find('.dimension-quantity');

      if ($(this).is(':checked')) {
        qtyInput.show().val('1');  // add this
      } else {
        qtyInput.hide().val('');
      }
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

    $('#submitter_standort_exception').on('change', function () {
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

    $('.summernote').summernote({
      height: 150,
      lang: 'de-DE'
    });


    let selectedFiles = []; // Store selected files

    $("#attachments").on("change", function (e) {
      let files = e.target.files;

      // Append new files without removing old ones
      for (let i = 0; i < files.length; i++) {
        selectedFiles.push(files[i]);
      }

      updateFilePreview(); // Update preview UI
    });

    function updateFilePreview() {
      $("#file-preview").empty(); // Clear preview UI

      selectedFiles.forEach((file, index) => {
        let reader = new FileReader();
        reader.onload = function (e) {
          if (file.type.includes("image")) {
            $("#file-preview").append(
              `<div class="p-2">
                            <img src="${e.target.result}" class="img-thumbnail" width="100">
                            <button type="button" class="btn btn-sm btn-danger remove-file" data-index="${index}">X</button>
                        </div>`
            );
          } else if (file.type === "application/pdf") {
            $("#file-preview").append(
              `<div class="p-2">
                            <i class="fas fa-file-pdf text-danger fa-3x"></i>
                            <p class="small">${file.name}</p>
                            <button type="button" class="btn btn-sm btn-danger remove-file" data-index="${index}">X</button>
                        </div>`
            );
          }
        };
        reader.readAsDataURL(file);
      });
    }

    // Remove a file when clicking "X"
    $(document).on("click", ".remove-file", function () {
      let index = $(this).data("index");
      selectedFiles.splice(index, 1); // Remove from array
      updateFilePreview();
    });

    // Append files manually to form before submit
    $("form").on("submit", function (e) {
      let fileInput = $("#attachments")[0];

      // Create new DataTransfer to manually add files
      let dataTransfer = new DataTransfer();
      selectedFiles.forEach(file => dataTransfer.items.add(file));

      fileInput.files = dataTransfer.files; // Assign files to input
    });

  });

  function uploadFile(file, context) {
    var formData = new FormData();
    formData.append('file', file);
    $.ajax({
      url: '/upload-pdf', // Your Laravel route
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },

      success: function (response) {
        if (response.success) {
          // Insert a link to the uploaded PDF
          context.invoke('editor.insertNode', $('<a href="' + response.url + '" target="_blank">View PDF: ' + file.name + '</a>')[0]);
        }
      },
      error: function (xhr) {
        alert('File upload failed!');
      }
    });
  }
</script>
@endsection