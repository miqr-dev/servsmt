@extends('layouts.admin_layout.admin_layout')

<style>
  /* chat profile photo */
  .rounded-circle {
    border-radius: 50%;
    width: 50px;
    height: 50px;
  }

  /* ticket underline */
  u {
    padding-bottom: 3px !important;
    text-decoration: none !important;
    border-bottom: 2px solid #661421 !important;
  }

  .admin_notes {
    height: 200px !important;
  }

  #wrapNext+div {
    white-space: break-spaces !important;
  }
</style>

@section('content')
@include('tickets.layout_ticket.header',['title'=>''])

<!-- Main content -->
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <div class="row mx-auto">
              <!-- first card -->
              <div class="col-lg-4">
                <div class="card card-primary card-outline">
                  <div class="card-body box-profile form-group">
                    <div class="row">
                      <div class="form-group col-md-12">
                        @if($ticket->deleted_at)
                        <p class="text-center lead">Erledigt von </p>
                        <h3 class="text-center" style="color: #661421;"><b>{{$ticket->done_by}}</b></h3>
                        @else
                        <p class="text-center lead">Zugewiesen an </p>
                        <h3 class="text-center" style="color: #661421;"><b>{{$ticket->user['username']}}</b></h3>
                        @endif
                      </div>
                      @if($ticket->deleted_at)
                      <div class="form-group col-md-12">
                        <form action="{{ route ('ticket.restore',$ticket->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-primary col-md-12">Wiederherstellen</button>
                        </form>
                      </div>
                      @else
                      @if(auth()->user()->hasRole('Super_Admin'))
                      <div class="form-group col-md-12 form-group mb-1">
                        <button class="col-md-12 btn btn-secondary" id="reminder-button" type="button">Reminder</button>
                      </div>
                      @if($ticket->problem_type === 'Neuer Mitarbeiter')
                      <div class="form-group col-md-12 mb-1">
                        <form action="{{ route ('ticket.delete2',$ticket->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-success col-md-12" value="Delete">Erledigt 2</button>
                        </form>
                      </div>
                      @else
                      <div class="form-group col-md-12 mb-1">
                        <form action="{{ route ('ticket.delete',$ticket->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-success col-md-12" value="Delete">Erledigt</button>
                        </form>
                      </div>
                      @if(is_null ($ticket->on_location))
                      <div class="form-group col-md-12">
                        <button type="button" class="btn btn-primary col-md-12 on_location">Vor Ort</button>
                      </div>
                      @else
                      <div class="form-group col-md-12">
                        <button type="button" class="btn btn-danger col-md-12 on_location_reverse">Nein, ab Erfurt
                          möglich</button>
                      </div>
                      @endif
                      <div class="form-group col-md-12">
                        <select name="assignedTo" class="assignTo btn col-md-12 bg-dark text-white text-center">
                          <option value="">Zuweisen</option>
                          @foreach($admins as $admin)
                          <option value="{{ $admin->id }}" {{$admin->id == $ticket->assignedTo ? 'selected' : '' }}>{{
                            $admin->username }}</option>
                          @endforeach
                        </select>
                      </div>
                      @endif
                      @endif
                      @endif
                      <div class="form-group col-md-6">
                        <label for="submitter"> Ersteller</label>
                        <input type="text" class="form-control" name="submitter"
                          value="{{@$ticket->subUser->vorname}} {{@$ticket->subUser->name}}" readonly>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="ticket_created_at"> Am</label>
                        <input type="text" class="form-control" name="ticket_created_at"
                          value="{{$createdAt->format('d M Y')}}" readonly>
                      </div>
                    </div>
                    <div class="row">
                      <div class="form-group col-md-6">
                        <label for="submitter_standort"> Standort</label>
                        <input type="text" class="form-control" name="submitter_standort" id="submitter_standort"
                          value="{{@$ticket->subUser->ort}}" readonly>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="submitter_adresse">Adresse</label>
                        <input type="text" class="form-control" name="submitter_adresse"
                          value="{{@$ticket->subUser->straße}}" readonly>
                      </div>
                    </div>
                    <!-- Ticket Type -->
                    <div class="row">
                      <input type="hidden" id="ticket_id_priority" value="{{$ticket->id}}">
                      <div class="form-group col-md-12">
                        <label for="priority"> Priorität</label>
                        @if(auth()->user()->hasRole('Super_Admin'))
                        <select class="custom-select" name="priority" id="priority">
                          @foreach($ticket_priority as $priority)
                          <option value="{{$priority->id}}" {{$priority->id == $ticket->priority_id ? 'selected' :
                            ''}}>{{$priority->name}}</option>
                          @endforeach
                        </select>
                        @else
                        <input type="text" class="form-control" readonly value="{{@$ticket->ticket_priority->name}}">
                        @endif

                      </div>
                      @if(auth()->user()->hasRole('Super_Admin'))
                      <div class="form-group col-md-12">
                        <label for="ticket_status"> Status</label>
                        <select class="custom-select" name="ticket_status" id="ticket_status">
                          @foreach($ticket_status as $status)
                          <option value="{{$status->id}}" {{$status->id == $ticket->ticket_status_id ? 'selected' :
                            ''}}>{{$status->name}}</option>
                          @endforeach
                        </select>
                      </div>
                      @endif
                      <div class="form-group col-md-12">
                        <label for="tel_number"> Telefon</label>
                        <input type="text" class="form-control" name="tel_number" value="{{$ticket->tel_number}}"
                          readonly>
                      </div>
                      <div class="form-group col-md-12">
                        <label for="custom_tel_number"> Aktuelle Rufnummer</label>
                        <input type="text" class="form-control" name="custom_tel_number"
                          value="{{@$ticket->custom_tel_number}}">
                      </div>
                    </div>
                    <div id="output"></div>
                    <!-- /.card-body -->
                  </div>
                </div>
              </div>
              <!--end first card -->
              <!-- second card -->
              <div class="col-lg-8">
                <div class="card card-primary card-outline">
                  <div class="card-body box-profile form-group">
                    <div class="row">
                      <!-- PC & Laptop -->

                      @include($blade_name)

                      <div class="col-md-12 invoice-col mt-2">
                        <strong style="color:#661421;">Beschreibung <i class="far fa-comment-dots fa-lg"></i></strong>
                        <div class="col-md-12 mt-2 wrapNext" id="wrapNext">
                          {!!@$ticket->notizen!!}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-12 d-flex justify-content-between">
                    <!-- Content  -->

                  </div>
                </div>
                <!-- <div class="row">
                        <div class="col-lg-6">
                          <div class="col-md-12 ml-3">
                          </div>
                        </div>
                        @if(auth()->user()->hasRole('Super_Admin'))
                        <div class="col-lg-6">
                          <div class="form-group col-md-12">
                            <label for="admin_notes"> Interne Notizen </label>
                            <textarea class="form-control admin_notes" id="admin_notes" name="admin_notes"  style="resize:none;">{{ @$ticket->admin_notes ? $ticket->admin_notes : ''  }}</textarea>
                          </div>
                        </div>
                        @endif
                      </div> -->
                <!--end for second row of second card-->
                <div class="row">
                  <div class="col-lg-12">
                    <div class="form-group col-md-6 col-lg-12 ml-3">
                      <label for="beschreibung"> Kommentar </label>
                    </div>
                    <div class="col-md-12 ml-3">
                      @comments(['model' => $ticket])
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="reminder-modal" tabindex="-1" role="dialog" aria-labelledby="reminderModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reminderModalLabel">Set Reminder</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="reminder-form">
              <div class="form-group">
                <label for="reminder-date">Date</label>
                <input type="date" class="form-control" id="reminder-date" required>
              </div>
              <button type="submit" class="btn btn-primary">Set Reminder</button>
            </form>
          </div>
        </div>
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

  $(document).ready(function () {

    $('#reminder-button').click(function () {
      $('#reminder-modal').modal('show');
    });

    // Handle the submission of the reminder form
    $('#reminder-form').submit(function (e) {
      e.preventDefault();
      const date = $('#reminder-date').val();
      const ticketId = $('#ticket_id_priority').val();
      $.ajax({
        type: 'post',
        url: '{{ route("ticket.setReminder") }}',
        data: {
          date: date,
          ticketId: ticketId,
        },
        success: function (result) {
          $('#reminder-modal').modal('hide');
          // You can add code here to update the UI with the new reminder
        }
      });
    });
  });


  let ticket_id = $('#ticket_id_priority').val();
  $(document).on('change', '#priority', function () {
    let priority = $(this).val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.ticketPriority')}}",
      data: { "priority": priority, "ticket_id": ticket_id },
      success: function (result) {
      }
    });
  });

  $(".on_location").click(function () {
    let ticket_id = $('#ticket_id_priority').val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.on_location')}}",
      data: { "ticket_id": ticket_id },
      success: function (result) {
        window.location.reload();
      }
    });
  });

  $(".on_location_reverse").click(function () {
    let ticket_id = $('#ticket_id_priority').val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.on_location_reverse')}}",
      data: { "ticket_id": ticket_id },
      success: function (result) {
        window.location.reload();
      }
    });
  });

  $(document).on('change', '.assignTo', function () {
    let ticket_id = $('#ticket_id_priority').val();
    let assignedTo = $(this).val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.assignedTo')}}",
      data: { "assignedTo": assignedTo, "ticket_id": ticket_id },
      success: function (result) {
      }
    });
  });


  $(document).on('change', '#ticket_status', function () {
    let status = $(this).val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.ticketStatus')}}",
      data: { "status": status, "ticket_id": ticket_id },
      success: function (result) {
      }
    });
  });

  // $('.show_confirm_participant').on('click', function (e) {
  //           e.preventDefault();
  //           let participant = $(this).data('id');
  //           console.log(participant);
  //           Swal.fire({
  //               title: 'sind Sie sicher ?',
  //               text: "Sie können dies nicht rückgängig machen!",
  //               icon: 'warning',
  //               showCancelButton: true,
  //               confirmButtonColor: '#661421',
  //               cancelButtonColor: '#d33',
  //               confirmButtonText: 'Ja, löschen !',
  //               cancelButtonText: 'Nein !'
  //           }).then((result) => {
  //               if (result.isConfirmed) {
  //                   $('#delete-participant-form').submit();
  //               }
  //           })
  //       });


  var submitter_standort = $('#submitter_standort').val();

  $(document).on('blur', '.password', function () {

    let password_id = $(this).attr('id');
    let username = $(this).val();
    console.log(submitter_standort)
    $.ajax({
      type: "post",
      url: "{{route('participant_username_update')}}",
      data: { "password_id": password_id, "username": username, "submitter_standort": submitter_standort },
      success: function (result) {

      }
    });
  })

  var timer = null;
  $('#admin_notes').keydown(function () {
    clearTimeout(timer);
    timer = setTimeout(doStuff, 1000)
  });

  function doStuff() {
    let ticket_id = $('#ticket_id_priority').val();
    let $adminNotes = $('#admin_notes').val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.admin_notes')}}",
      data: { "adminNotes": $adminNotes, "ticket_id": ticket_id },
      success: function (result) {
      }
    });
  }

  var timer = null;
  $('#employee_username').keydown(function () {
    clearTimeout(timer);
    timer = setTimeout(doStuff2, 1000)
  });

  function doStuff2() {
    let ticket_id = $('#ticket_id_priority').val();
    let $employee_username = $('#employee_username').val();
    console.log($employee_username);
    $.ajax({
      type: "post",
      url: "{{route('ticket.employee_username')}}",
      data: { "employee_username": $employee_username, "ticket_id": ticket_id },
      success: function (result) {
      }
    });
  }

  var timer = null;
  $('#employee_password').keydown(function () {
    clearTimeout(timer);
    timer = setTimeout(doStuff3, 1000)
  });

  function doStuff3() {
    let ticket_id = $('#ticket_id_priority').val();
    let $employee_password = $('#employee_password').val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.employee_password')}}",
      data: { "employee_password": $employee_password, "ticket_id": ticket_id },
      success: function (result) {
      }
    });
  }
  var timer = null;
  $('#employee_email').keydown(function () {
    clearTimeout(timer);
    timer = setTimeout(doStuff4, 1000)
  });

  function doStuff4() {
    let ticket_id = $('#ticket_id_priority').val();
    let $employee_email = $('#employee_email').val();
    $.ajax({
      type: "post",
      url: "{{route('ticket.employee_email')}}",
      data: { "employee_email": $employee_email, "ticket_id": ticket_id },
      success: function (result) {
      }
    });
  }

</script>
@endsection