@extends('layouts.admin_layout.admin_layout')

<style>
  .button-group {
    margin-right: 20px;
  }

  .item-selected {
    border: 1px solid #ced4da;
    padding: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
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

  .form-check-input[type="checkbox"] + .form-check-label {
    position: relative;
    cursor: pointer;
    padding: 0;
  }

  .form-check-input[type="checkbox"] + .form-check-label:before {
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

  .form-check-input[type="checkbox"]:checked + .form-check-label:before {
    background: #65A30D;
    border-color: #65A30D;
  }

  .form-check-input[type="checkbox"]:checked + .form-check-label:after {
    content: '';
    position: absolute;
    left: 6px;
    top: 12px;
    background: white;
    width: 2px;
    height: 2px;
    box-shadow:
      2px 0 0 white,
      4px 0 0 white,
      4px -2px 0 white,
      4px -4px 0 white,
      4px -6px 0 white,
      4px -8px 0 white;
    transform: rotate(45deg);
  }
</style>

@section('content')
@include('tickets.layout_ticket.header', [
  'title' => 'Zertifizierung & Qualitätsmanagement',
  'colorClass' => 'ticket_header_korso',
  'buttonClass' => 'btn-outline-green'
])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
        <div class="card card-thirdary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{ route('form_store_korso') }}" method="post" enctype="multipart/form-data">
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
                      <input type="hidden" name="problem_type" value="Zertifizierung & Qualitätsmanagement">

                      <div class="card-body box-profile form-group">
                        <div class="row my-4 p-2">
                          <div class="col-lg-12">

                            <div class="form-group col-lg-12">
                              <label>Was brauchen Sie ?</label>
                              <div class="row">
                                <div class="col-md-6">
                                  @foreach($zertifizierung_items->take(9) as $item)
                                    <div class="form-check mb-2">
                                      <input
                                        class="form-check-input zertifizierung-checkbox"
                                        type="checkbox"
                                        name="zertifizierung_item_id"
                                        value="{{ $item->id }}"
                                        id="zertifizierung_item_{{ $item->id }}"
                                        data-location-needed="{{ $item->location_needed }}"
                                        data-massnahme-needed="{{ $item->massnahme_needed }}"
                                        data-infonet="{{ $item->name === 'Fehlermeldung/Aktualisierung InfoNet' ? 1 : 0 }}"
                                      >
                                      <label class="form-check-label" for="zertifizierung_item_{{ $item->id }}">
                                        {{ $item->name }}
                                      </label>
                                    </div>
                                  @endforeach
                                </div>

                                <div class="col-md-6">
                                  @foreach($zertifizierung_items->slice(9) as $item)
                                    <div class="form-check mb-2">
                                      <input
                                        class="form-check-input zertifizierung-checkbox"
                                        type="checkbox"
                                        name="zertifizierung_item_id"
                                        value="{{ $item->id }}"
                                        id="zertifizierung_item_{{ $item->id }}"
                                        data-location-needed="{{ $item->location_needed }}"
                                        data-massnahme-needed="{{ $item->massnahme_needed }}"
                                        data-infonet="{{ $item->name === 'Fehlermeldung/Aktualisierung InfoNet' ? 1 : 0 }}"
                                      >
                                      <label class="form-check-label" for="zertifizierung_item_{{ $item->id }}">
                                        {{ $item->name }}
                                      </label>
                                    </div>
                                  @endforeach
                                </div>
                              </div>

                              <!-- InfoNet Warning (hidden by default) -->
                              <div id="infonet-warning" class="alert alert-warning d-none mt-2" role="alert" aria-live="polite">
                                <i class="fas fa-exclamation-triangle"></i>
                                Bitte geben Sie den genauen Link an oder fügen Sie einen Screenshot bei.
                              </div>
                            </div>

                            <div class="row">
                              <div class="form-group col-md-6 d-none" id="standort_group">
                                <label for="printer_place">
                                  Standort
                                  <i class="fa-solid fa-thumbtack fa-lg" style="color: #65A30D;"></i>
                                </label>
                                <select class="custom-select form-control mb-2" id="printer_place" name="location_id" disabled>
                                </select>
                              </div>

                              <div class="form-group col-md-6 d-none" id="massnahme_group">
                                <label for="massnahme_id">Maßnahme auswählen</label>
                                <select class="custom-select" name="massnahme_id" id="massnahme_id">
                                  <option value="">-- Maßnahme suchen --</option>
                                  @foreach($massnahmes as $massnahme)
                                    <option value="{{ $massnahme->id }}">{{ $massnahme->name }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>

                          </div>
                        </div>

                        <div class="form-group">
                          <label for="notizen"> Notizen</label>
                          <textarea type="text" name="notizen" class="form-control summernote"></textarea>

                          <div class="form-group">
                            <label for="attachments" class="font-weight-bold">
                              Anhänge <small class="text-muted">(Bilder oder PDFs hochladen)</small>
                            </label>

                            <div class="custom-file">
                              <input
                                type="file"
                                name="attachments[]"
                                id="attachments"
                                class="custom-file-input"
                                multiple
                                accept="image/*,application/pdf"
                              >
                              <label class="custom-file-label" for="attachments" data-browse="Suchen">Dateien auswählen...</label>
                            </div>

                            <small class="form-text text-muted mt-1">Maximale Größe: 5MB pro Datei</small>

                            <!-- Preview Area -->
                            <div id="file-preview" class="d-flex flex-wrap mt-3 border rounded p-2 bg-light"></div>
                          </div>
                        </div>

                        <!-- (Duplicate preview kept to match original structure) -->
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
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</section><!-- /.content -->
@endsection

@section('script')
<script>
  $(document).ready(function () {

    $(document).on('change', '.zertifizierung-checkbox', function () {
      // Allow single selection only
      $('.zertifizierung-checkbox').not(this).prop('checked', false);

      const locationNeeded = $(this).data('location-needed');
      const massnahmeNeeded = $(this).data('massnahme-needed');

      // Toggle Standort
      if (locationNeeded) {
        $('#printer_place').prop('disabled', false).closest('.form-group').removeClass('d-none');
        $('#submitter_standort_exception').prop('disabled', false).closest('.form-group').removeClass('d-none');
      } else {
        $('#printer_place').prop('disabled', true).closest('.form-group').addClass('d-none');
        $('#submitter_standort_exception').prop('disabled', true).closest('.form-group').addClass('d-none');
      }

      // Toggle Maßnahme
      if (massnahmeNeeded) {
        $('#massnahme_group').removeClass('d-none');
      } else {
        $('#massnahme_group').addClass('d-none');
        $('#massnahme_id').val('');
      }

      // If none are selected, hide all extra fields
      if (!$('.zertifizierung-checkbox:checked').length) {
        $('#printer_place').prop('disabled', true).closest('.form-group').addClass('d-none');
        $('#submitter_standort_exception').prop('disabled', true).closest('.form-group').addClass('d-none');
        $('#massnahme_group').addClass('d-none');
        $('#massnahme_id').val('');
        // Hide InfoNet warning if nothing selected
        $('#infonet-warning').addClass('d-none');
        return;
      }

      // ----- Additional logic -----
      var itemName = $(this).closest('.form-check').find('.form-check-label').text().trim();
      if (itemName === "Beantragung einer Maßnahmenummer") {
        // Make attachments required
        $("#attachments").attr("required", "required");
      } else {
        $("#attachments").removeAttr("required");
      }

      // Show the InfoNet warning only if that specific item is selected
      const showInfoNetWarning = $(this).is(':checked') && ($(this).data('infonet') == 1);
      $('#infonet-warning').toggleClass('d-none', !showInfoNetWarning);
      // ----- End Additional logic -----
    });

    $('#massnahme_id').select2({
      placeholder: "Maßnahme suchen...",
      width: '100%'
    });

    function resetDropdown(id, placeholder) {
      $(`#${id}`).empty().append(new Option(placeholder, ''));
    }

    let selectAddresslisten = [];
    let roomlisten = [];

    resetDropdown('printer_place', "Standort...");
    resetDropdown('printer_room', "Raum...");

    $.ajax({
      type: "get",
      url: "{{ route('room_list') }}",
    }).done(function (data) {
      $("body #printer_place").append('<optgroup label="' + data.place.pnname + '" id="' + data.place.id + '"></optgroup>');

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
        $("body #printer_place").append('<optgroup label="' + data.place.pnname + '" id="' + data.place.id + '"></optgroup>');

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
    $("form").on("submit", function () {
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
      error: function () {
        alert('File upload failed!');
      }
    });
  }
</script>
@endsection
