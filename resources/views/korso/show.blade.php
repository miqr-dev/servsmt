@extends('layouts.admin_layout.admin_layout')
<style>
  .rounded-circle {
    border-radius: 50%;
    width: 50px;
    height: 50px;
  }

  .korso-comment-wrapper {
    position: relative;
  }

  .korso-internal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.4);
    z-index: 9999;
    /* increased to block all interaction */
    cursor: not-allowed;
  }


  .strike {
    text-decoration: line-through;
  }

  .internal-comments-container {
    width: 50%;
    float: right;
    max-width: 50%;
    /* Ensures it doesn't exceed half of the parent container */
  }

  .internal-comment {
    display: flex;
    align-items: center;
    /* Ensures vertical alignment */
    justify-content: space-between;
    /* Keeps text on the left and buttons on the right */
    padding: 5px 10px;
    border-bottom: 1px solid #ddd;
    gap: 10px;
    width: 100%;
    /* Makes sure it fills the container */
  }

  .internal-comment div:first-child {
    flex-grow: 1;
    /* Allows text to take available space */
    word-break: break-word;
    /* Prevents text from pushing buttons */
  }

  .comment-actions {
    white-space: nowrap;
    /* Prevents buttons from wrapping */
    display: flex;
    gap: 8px;
    align-items: center;
  }

  /* .swal2-input-custom {
    width: 90% !important;
    font-size: 16px !important;
    padding: 12px !important;
    border: 1px solid #ccc !important;
    border-radius: 5px !important;
  } */
</style>



@section('content')
@include('tickets.layout_ticket.header', [
'title' => 'Korso Ticket',
'colorClass' => 'ticket_header_korso',
'buttonClass' => 'btn-outline-green' // New class for button color
])

@php
$isDone = !is_null($korso->deleted_at);
@endphp
<div class="container">
  @if(auth()->user()->hasAnyRole('Super_Admin','Korso_ma','Korso_Admin'))
  <!-- Add the Download PDF button at the top -->
  <div class="mb-3 float-right">
    <a href="{{ route('korso.download.pdf', $korso->id) }}" class="btn btn-outline-green" target="_blank">
      PDF
    </a>
  </div>
  @endif
  <div class="row">
    <!-- Left Section (Assignment & Status) -->
    <div class="col-md-4">
      <div class="card shadow-sm mb-4">
        <div class="card-header text-center"
          style="color:#65A30D; border-top: 3px solid #65A30D; font-weight: bold; font-size: 1.2rem;">
          {{ $isDone ? 'Erledigt von' : 'Zugewiesen an' }}
        </div>
        <div class="card-body text-center">
          <h5 class="font-weight-bold mb-2">
            {{ $isDone ? $korso->doneByUser->name ?? 'Unbekannt' : optional($korso->assignedUser)->name ??
            'Nicht zugewiesen' }}
          </h5>

          @if($isDone)
          <form action="{{ route('korso.restore', $korso->id) }}" method="POST">
            @csrf
            <button class="btn btn-primary w-100" id="restore-ticket"
              data-id="{{ $korso->id }}">Wiederherstellen</button>
          </form>
          @else
          @if(auth()->user()->hasAnyRole('Super_Admin','Korso_ma','Korso_Admin'))
          <button class="btn btn-success btn-block" id="mark-done-btn" data-id="{{ $korso->id }}">Erledigt</button>
          @endif

          <select class="form-control mt-2 assign-user" data-id="{{ $korso->id }}"
            @if(!auth()->user()->hasAnyRole('Super_Admin','Korso_ma','Korso_Admin') || $isDone) disabled @endif>
            <option value="">Zuweisen</option>
            @foreach($korso_ma_users as $korso_ma)
            <option value="{{ $korso_ma->id }}" {{ $korso->assignedTo == $korso_ma->id ? 'selected' : '' }}>
              {{ strtoupper(substr($korso_ma->vorname, 0, 1)) }}. {{ $korso_ma->name }}
            </option>
            @endforeach
          </select>
          @endif
        </div>
      </div>

      <!-- Ticket Info -->
      <div class="card shadow-sm">
        <div class="card-header"
          style="color:#65A30D; border-top: 3px solid #65A30D; font-weight: bold; font-size: 1.2rem;">
          Ticket Informationen
        </div>
        <div class="card-body">
          <div class="row mb-2">
            <div class="col-md-6">
              <label class="font-weight-bold">Ersteller:</label>
              <input type="text" class="form-control" value="{{ $korso->subUser->name ?? 'Unknown' }}" readonly>
            </div>
            <div class="col-md-6">
              <label class="font-weight-bold">Am:</label>
              <input type="text" class="form-control" value="{{ $korso->created_at->format('d M Y') }}" readonly>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-md-6">
              <label class="font-weight-bold">Standort:</label>
              <input type="text" class="form-control" value="{{ $korso->subUser->ort ?? '—' }}" readonly>
            </div>
            <div class="col-md-6">
              <label class="font-weight-bold">Position:</label>
              <input type="text" class="form-control" value="{{ $korso->subUser->position ?? '—' }}" readonly>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-md-6">
              <label class="font-weight-bold">Abteilung:</label>
              <input type="text" class="form-control" value="{{ $korso->subUser->abteilung ?? '—' }}" readonly>
            </div>
            <div class="col-md-6">
              <label class="font-weight-bold">Telefon:</label>
              <input type="text" class="form-control" value="{{ $korso->tel_number }}" readonly>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-md-12">
              <label class="font-weight-bold">Adresse:</label>
              <input type="text" class="form-control" value="{{ $korso->submitter_adresse }}" readonly>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-md-12">
              <label class="font-weight-bold">Priorität:</label>
              <select class="form-control" @if(!auth()->user()->hasAnyRole('Super_Admin','Korso_ma','Korso_Admin'))
                disabled @endif>
                <option {{ $korso->priority == 3 ? 'selected' : '' }}>Hoch</option>
                <option {{ $korso->priority == 2 ? 'selected' : '' }}>Normal</option>
                <!-- <option {{ $korso->priority == 1 ? 'selected' : '' }}>Niedrig</option> -->
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <label class="font-weight-bold">Status:</label>
              <select class="form-control" id="ticket_status_id" name="ticket_status_id"
                @if(!auth()->user()->hasAnyRole('Super_Admin','Korso_ma','Korso_Admin')) disabled @endif>
                @foreach($ticket_statuses as $status)
                <option value="{{ $status->id }}" {{ $korso->ticket_status_id == $status->id ? 'selected' : '' }}>
                  {{ $status->name }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Section (Ticket Details) -->
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header"
          style="color:#65A30D; border-top: 3px solid #65A30D; font-weight: bold; font-size: 1.2rem;">
          {{ strtoupper($korso->problem_type) }}
          @if($korso->problem_type === 'Onlinemarketing' && $korso->onlinemarketingItem)
          <span class="badge badge-info ml-2">{{ $korso->onlinemarketingItem->name }}</span>
          @elseif($korso->problem_type === 'Zertifizierung & Qualitätsmanagement' && $korso->zertifizierungItem)
          <span class="badge badge-info ml-2">{{ $korso->zertifizierungItem->name }}</span>
          @endif

        </div>
        <div class="card-body">
          <!-- Bestellung Flyer -->

          <div class="row">

            <!-- Bestellung Flyer -->
            <!-- <div class="col-md-6">
              @if($korso->kcourses->isNotEmpty())
              <h5 class="text-success font-weight-bold mb-2">
                <i class="fas fa-box"></i> Bestellung Flyer
              </h5>
              <ul class="list-unstyled">
                @foreach($korso->kcourses as $kcourse)
                <li class="d-flex justify-content-between align-items-center p-1">
                  <span class="text-dark">{{ $kcourse->payer->name }} <i class="far fa-hand-point-right"></i> {{
                    $kcourse->name }}</span>
                  <span class="badge badge-success font-weight-bold px-3 py-2">{{ $kcourse->pivot->quantity }}</span>
                </li>
                @endforeach
              </ul>
              @endif
            </div> -->

            <!-- Bestellte Artikel -->
            <div class="col-md-6">
              @if($korso->korsoItems->isNotEmpty())
              <h5 class="text-success font-weight-bold mb-2">
                <i class="fas fa-shopping-cart"></i> Bestellte Artikel
              </h5>
              <ul class="list-unstyled">
                @foreach($korso->korsoItems as $item)
                <li class="d-flex flex-column p-1 border-bottom">
                  <div class="d-flex justify-content-between align-items-center">
                    <span>{{ ucwords(str_replace(['_', '-'], [' ', '-'], $item->item_name)) }}</span>
                    <span class="badge badge-success font-weight-bold px-3 py-2">{{ $item->quantity }}</span>
                  </div>
                  @if($item->item_name === 'Visitenkarten' && $item->details)
                  @php
                  $details = json_decode($item->details, true);
                  @endphp
                  <ul class="list-unstyled mt-2 ml-3" style="font-size: 0.95rem; color: #333;">
                    @if(!empty($details['name']))
                    <li class="mb-1"><strong style="width: 90px; display: inline-block;">Name:</strong> {{
                      $details['name'] }}</li>
                    @endif
                    @if(!empty($details['email']))
                    <li class="mb-1"><strong style="width: 90px; display: inline-block;">Email:</strong> {{
                      $details['email'] }}</li>
                    @endif
                    @if(!empty($details['telephone']))
                    <li class="mb-1"><strong style="width: 90px; display: inline-block;">Telefon:</strong> {{
                      $details['telephone'] }}</li>
                    @endif
                    @if(!empty($details['position']))
                    <li class="mb-1"><strong style="width: 90px; display: inline-block;">Position:</strong> {{
                      $details['position'] }}</li>
                    @endif
                    @if(!empty($details['adresse']))
                    <li class="mb-1"><strong style="width: 90px; display: inline-block;">Adresse:</strong> {{
                      $details['adresse'] }}</li>
                    @endif
                    @if(!empty($details['fax']))
                    <li class="mb-1"><strong style="width: 90px; display: inline-block;">Fax:</strong> {{
                      $details['fax'] }}</li>
                    @endif
                  </ul>
                  @endif

                </li>
                @endforeach
              </ul>
              @endif
            </div>

          </div> <!-- End row -->

          <!-- Beschreibung Section -->
          <div class="mt-3">
            <h5 class="text-success font-weight-bold mb-2">
              <i class="fas fa-clipboard"></i> Beschreibung
            </h5>
            <p class="mb-0" style="font-size: 1rem;">
              {!! $korso->notizen ?? 'Keine Beschreibung verfügbar.' !!}
            </p>
          </div>

          @if($korso->problem_in_city || @$korso->location->place->pnname)
          <div class="mt-3">
            <h5 class="text-success font-weight-bold mb-2">
              <i class="fas fa-map-marker-alt"></i> Standort
            </h5>
            <p class="mb-0" style="font-size: 1rem;">
              {{ $korso->problem_in_city ?: @$korso->location->place->pnname }}
            </p>
          </div>
          @endif

          @if($korso->massnahme)
          <div class="mt-3">
            <h5 class="text-success font-weight-bold mb-2">
              <i class="fas fa-graduation-cap"></i> Maßnahme
            </h5>
            <p class="mb-0" style="font-size: 1rem;">
              {{ $korso->massnahme->name }}
            </p>
          </div>
          @endif

          <div class="row mt-4">
            <!-- Left Side: Attachments (Empty if no attachments) -->
            <div class="col-md-6">
              @if($korso->korsoAttachments->count() > 0)
              <h5 class="font-weight-bold">Anhänge</h5>
              <div class="d-flex flex-wrap">
                @foreach($korso->korsoAttachments as $attachment)
                <div class="p-2 position-relative" id="attachment-{{ $attachment->id }}">
                  @if(str_contains($attachment->file_type, 'image'))
                  <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                    <img src="{{ asset('storage/' . $attachment->file_path) }}" class="img-thumbnail" width="100">
                  </a>
                  @elseif($attachment->file_type === 'application/pdf')
                  <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                    <i class="fas fa-file-pdf text-danger" style="font-size: 100px;"></i>
                  </a>
                  @elseif(in_array($attachment->file_type, [
                  'application/msword',
                  'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                  ]))
                  <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                    <i class="fas fa-file-word text-primary" style="font-size: 100px;"></i>
                  </a>
                  @elseif(in_array($attachment->file_type, [
                  'application/vnd.ms-excel',
                  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                  ]))
                  <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                    <i class="fas fa-file-excel text-success" style="font-size: 100px;"></i>
                  </a>
                  @elseif(in_array($attachment->file_type, [
                  'application/vnd.ms-powerpoint',
                  'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                  ]))
                  <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                    <i class="fas fa-file-powerpoint text-warning" style="font-size: 100px;"></i>
                  </a>
                  @else
                  <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                    <i class="fas fa-file-alt text-muted" style="font-size: 100px;"></i>
                  </a>
                  @endif
                  {{-- Delete button for Korso_ma users --}}
                  @if(auth()->user()->hasRole('Korso_ma'))
                  <button type="button" class="btn btn-danger btn-sm delete-attachment" data-id="{{ $attachment->id }}"
                    style="position: absolute; top: 5px; right: 5px;">&times;</button>
                  @endif
                </div>
                @endforeach
              </div>
              @endif

            </div>

            <!-- Right Side: Internal Comments (Always Displayed) -->
            @if(auth()->user()->hasAnyRole('Super_Admin','Korso_ma','Korso_Admin'))
            <div class="col-md-6 korso-comment-wrapper">
              <h5 style="color:#65A30D;">Korso Interne Kommentare</h5>

              <input type="text" id="internalCommentInput" class="form-control mb-3 mt-1"
                placeholder="Neuen Kommentar schreiben...">

              <div id="internalCommentsList">
                @forelse($korso->internalComments as $comment)
                <div class="internal-comment d-flex justify-content-between align-items-center 
      {{ $comment->is_deleted ? 'text-muted font-italic strike' : '' }}" data-id="{{ $comment->id }}">
                  <div>
                    <strong>{{ $comment->user->name }}</strong>:
                    <span class="comment-text">{{ $comment->comment }}</span>
                    <small class="text-muted">({{ $comment->created_at->diffForHumans() }})</small>
                  </div>
                  @if($comment->user_id == auth()->id())
                  <div class="comment-actions">
                    @if($comment->is_deleted)
                    <i class="fas fa-undo reactivate-comment text-success"></i>
                    @else
                    <i class="fas fa-edit edit-comment text-secondary"></i>
                    <i class="fas fa-trash delete-comment text-danger"></i>
                    @endif
                  </div>
                  @endif
                </div>
                @empty
                <p class="text-muted">Keine internen Kommentare vorhanden.</p>
                @endforelse
              </div>

              @if($isDone)
              <div class="korso-internal-overlay"></div>
              @endif
            </div>
            @endif
            <div class="row">
              <div class="col-lg-12">
                <div class="form-group col-md-6 col-lg-12 ml-3">
                  <label for="beschreibung"> Kommentar </label>
                </div>
                <div class="col-md-12 ml-3">
                  @comments(['model' => $korso])
                </div>
              </div>
              @if(auth()->user()->hasRole('Korso_ma'))
              <div class="form-group mt-3">
                <label class="font-weight-bold"></label>
                <!-- Hidden file input -->
                <input type="file" id="uploadAttachments" name="attachments[]" class="d-none" multiple
                  accept="application/pdf,image/*,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                <!-- Button to trigger file selection -->
                <button type="button" id="uploadBtn" class="btn btn-secondary ml-2">Hochladen</button>
                <!-- Optional preview area for selected files -->
                <div id="uploadPreview" class="mt-2"></div>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kommentar bearbeiten</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editCommentId">
        <textarea id="editCommentInput" class="form-control" rows="3"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
        <button type="button" class="btn btn-primary" id="saveEditedComment">Speichern</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>

  $(document).ready(function () {
    // When the "Hochladen" button is clicked, trigger the hidden file input
    $('#uploadBtn').click(function () {
      $('#uploadAttachments').trigger('click');
    });

    // When files are selected, send them via AJAX to the server
    $('#uploadAttachments').on('change', function () {
      let files = this.files;
      if (files.length === 0) {
        return;
      }

      // Optionally, display file preview (this part can be customized)
      $('#uploadPreview').empty();
      for (let i = 0; i < files.length; i++) {
        $('#uploadPreview').append('<p>' + files[i].name + '</p>');
      }

      let formData = new FormData();
      // Append each file to formData
      for (let i = 0; i < files.length; i++) {
        formData.append('attachments[]', files[i]);
      }
      // Append CSRF token
      formData.append('_token', '{{ csrf_token() }}');

      // Send the AJAX request to a new route (see below)
      $.ajax({
        url: "{{ route('korso.attachment.upload', $korso->id) }}", // New route with the korso ticket id
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          toastr.success(response.message);
          // Optionally, reload or update the attachments section on the page after upload
          setTimeout(function () {
            location.reload();
          }, 1500);
        },
        error: function (xhr) {
          toastr.error("File upload failed!");
        }
      });
    });
  });

  $(document).on('click', '.delete-attachment', function () {
    var attachmentId = $(this).data('id');
    var korsoId = "{{ $korso->id }}";
    var $attachmentContainer = $("#attachment-" + attachmentId);

    if (confirm("Möchten Sie diesen Anhang wirklich löschen?")) {
      $.ajax({
        url: "/korso/" + korsoId + "/attachment/" + attachmentId,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          _method: 'DELETE'
        },
        success: function (response) {
          toastr.success(response.message);
          $("#attachment-" + attachmentId).remove();
        },
        error: function (xhr) {
          console.error(xhr.responseText); // For debugging
          toastr.error("Löschen des Anhangs fehlgeschlagen!");
        }
      });
    }
  });


  $('.assign-user').change(function () {
    let ticketId = $(this).data('id');
    let userId = $(this).val();

    $.post("{{ route('korso.assign') }}", {
      _token: "{{ csrf_token() }}",
      ticket_id: ticketId,
      user_id: userId
    })
      .done(function () {
        toastr.success('Benutzer erfolgreich zugewiesen.');
        setTimeout(() => {
          window.location.href = "{{ route('korso.dashboard') }}";
        }, 1500); // Delay to let the toast show briefly
      })
      .fail(function () {
        toastr.error('Fehler beim Zuweisen des Benutzers.');
      });
  });

  $(document).on('keypress', '#internalCommentInput', function (e) {
    if (e.which == 13) {
      let comment = $(this).val();
      let korso_id = "{{ $korso->id }}";

      $.post("{{ route('korso.comments.store') }}", {
        _token: "{{ csrf_token() }}",
        korso_id: korso_id,
        comment: comment
      }, function (data) {
        $('#internalCommentsList').prepend(`
    <div class="internal-comment d-flex justify-content-between align-items-center" data-id="${data.id}">
        <div>
            <strong>${data.user}</strong>: <span class="comment-text">${data.comment}</span>
            <small class="text-muted">(${data.created_at})</small>
        </div>
        <div class="comment-actions">
            <i class="fas fa-edit edit-comment text-secondary"></i>
            <i class="fas fa-trash delete-comment text-danger"></i>
        </div>
    </div>
`);
        $('#internalCommentInput').val('');
      });
    }
  });

  $(document).on('click', '.edit-comment', function () {
    let commentElement = $(this).closest('.internal-comment');
    let commentText = commentElement.find('.comment-text').text().trim();
    let commentId = commentElement.data('id');

    $('#editCommentId').val(commentId);
    $('#editCommentInput').val(commentText);

    // Automatically resize textarea based on text length
    let textArea = $('#editCommentInput');
    textArea.css({
      'height': 'auto',
      'min-height': '50px'
    }).height(textArea[0].scrollHeight);

    $('#editCommentModal').modal('show');
  });

  // Resize textarea when typing
  $('#editCommentInput').on('input', function () {
    $(this).height(0).height(this.scrollHeight);
  });

  // Save edited comment
  $('#saveEditedComment').click(saveEditedComment);
  $('#editCommentInput').keypress(function (e) {
    if (e.which == 13 && !e.shiftKey) { // Enter key without Shift
      e.preventDefault();
      saveEditedComment();
    }
  });

  // Function to save the comment
  function saveEditedComment() {
    let commentId = $('#editCommentId').val();
    let updatedComment = $('#editCommentInput').val().trim();

    if (updatedComment === '') {
      alert('Kommentar darf nicht leer sein!');
      return;
    }

    $.post("{{ url('/korso/comments') }}/" + commentId, {
      _token: "{{ csrf_token() }}",
      _method: 'PATCH',
      comment: updatedComment
    }, function () {
      let commentElement = $('.internal-comment[data-id="' + commentId + '"]');
      commentElement.find('.comment-text').text(updatedComment);
      $('#editCommentModal').modal('hide');
    });
  }



  $(document).on('click', '.delete-comment', function () {
    let commentElement = $(this).closest('.internal-comment');
    let commentId = commentElement.data('id');

    $.post("{{ url('/korso/comments') }}/" + commentId + "/delete", {
      _token: "{{ csrf_token() }}",
      _method: 'PATCH'
    }, function () {
      commentElement.addClass('text-muted font-italic strike');
      commentElement.find('.comment-actions').html(`<i class="fas fa-undo reactivate-comment text-success"></i>`);
    });
  });

  $(document).on('click', '.reactivate-comment', function () {
    let commentElement = $(this).closest('.internal-comment');
    let commentId = commentElement.data('id');

    $.post("{{ url('/korso/comments') }}/" + commentId + "/restore", {
      _token: "{{ csrf_token() }}",
      _method: 'PATCH'
    }, function () {
      commentElement.removeClass('text-muted font-italic strike');
      commentElement.find('.comment-actions').html(`
            <i class="fas fa-edit edit-comment text-secondary"></i>
            <i class="fas fa-trash delete-comment text-danger"></i>
        `);
    });
  });

  $(document).on('click', '#mark-done-btn', function () {
    let korsoId = $(this).data('id');

    if (confirm('Möchten Sie dieses Ticket wirklich als erledigt markieren?')) {
      $.ajax({
        url: `/korso/${korsoId}/done`,
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function () {
          toastr.success('Ticket wurde als erledigt markiert.');
          setTimeout(() => {
            window.location.href = '{{ route("korso.dashboard") }}';
          }, 1500);
        },
        error: function () {
          toastr.error('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
        }
      });
    }
  });

  $(document).on('click', '#restore-ticket', function () {
    let korsoId = $(this).data('id');

    $.ajax({
      url: `/korso/${korsoId}/restore`,
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}'
      },
      success: function () {
        toastr.success('Ticket wiederhergestellt.');
        setTimeout(() => {
          location.reload(); // Refresh the page to get active state
        }, 1500);
      },
      error: function () {
        toastr.error('Fehler beim Wiederherstellen des Tickets.');
      }
    });
  });


  $('#ticket_status_id').change(function () {
    let statusId = $(this).val();
    let korsoId = {{ $korso-> id
  }};

  $.ajax({
    url: `/korso/update-status/${korsoId}`,
    method: 'POST',
    data: {
      _token: '{{ csrf_token() }}',
      ticket_status_id: statusId
    },
    success: function (res) {
      toastr.success(res.message);
    },
    error: function () {
      toastr.error('Fehler beim Aktualisieren des Status.');
    }
  });
});
</script>
@endsection
