@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header', [
'title' => 'Onlinemarketing',
'colorClass' => 'ticket_header_korso',
'buttonClass' => 'btn-outline-green'
])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-thirdary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{ route('form_store_korso') }}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="row mx-auto">
                @include('korso.layout.submitter')
                <div class="col-lg-10">
                  <div class="card card-thirdary card-outline">
                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Onlinemarketing">
                      <div class="card-body box-profile form-group">
                        <div class="form-group">
                          <label for="onlinemarketing_item">Was brauchen Sie ?</label>
                          <div class="row">
                            @foreach($onlinemarketingItems->chunk(15) as $chunk) {{-- Split into groups of 15 --}}
                            <div class="col-md-6"> {{-- Each column will contain max 15 items --}}
                              @foreach($chunk as $item)
                              <div class="custom-control custom-radio">
                                <input type="radio" id="item{{ $item->id }}" name="onlinemarketing_item"
                                  value="{{ $item->id }}" class="custom-control-input">
                                <label class="custom-control-label" for="item{{ $item->id }}">{{ $item->name }}</label>
                              </div>
                              @endforeach
                            </div>
                            @endforeach
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="notizen">Notizen</label>
                          <textarea name="notizen" class="form-control summernote"></textarea>
                        </div>
                        <div class="form-group">
                          <label for="attachments" class="font-weight-bold">Anhänge <small class="text-muted">(Bilder
                              oder PDFs hochladen)</small></label>

                          <div class="custom-file">
                            <input type="file" name="attachments[]" id="attachments" class="custom-file-input" multiple
                              accept="image/*,application/pdf">
                            <label class="custom-file-label" for="attachments">Dateien auswählen...</label>
                          </div>

                          <small class="form-text text-muted mt-1">Maximale Größe: 5MB pro Datei</small>

                          <!-- Preview Area -->
                          <div id="file-preview" class="d-flex flex-wrap mt-3 border rounded p-2 bg-light"></div>
                        </div>
                        <div id="file-preview" class="mt-3 d-flex flex-wrap"></div>
                        <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
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
  $(document).ready(function () {

    $('form').on('submit', function (e) {
      const $form = $(this);
      const $submitButton = $form.find('button[type="submit"], input[type="submit"]');

      if (!$('input[name="onlinemarketing_item"]:checked').val()) {
        e.preventDefault();
        alert("Bitte wählen Sie eine Option unter 'Was brauchen Sie ?' aus.");

        // Restore submit button since submission is canceled
        $submitButton.prop('disabled', false);
        $submitButton.html('Einreichen');

        return false;
      }
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