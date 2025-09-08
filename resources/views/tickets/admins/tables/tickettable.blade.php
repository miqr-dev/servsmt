@extends('layouts.admin_layout.admin_layout')

<style>
  table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
  table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
    left: 10px !important;
    text-indent: 0 !important;
    background-color: #661421 !important;
  }

  .vertical-divider {
    border-left: 1px solid #dee2e6;
    /* Bootstrap's border color */
    height: 30px;
    margin: auto 1rem;
    /* Spacing on the sides */
  }

  /* prevent blinking tooltip */
  .tooltip {
    pointer-events: none;
  }

  /* datatable - hidden column wrapping */
  .dtr-data {
    white-space: normal
  }

  .navbar {
    border-bottom: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
  }

  .nav-link {
    color: #661421;
    margin-right: 10px;
    /* Adjust spacing */
  }

  .nav-link:hover {
    color: #8b2e4f;
    /* Darker shade for hover effect */
  }


  .badge {
    font-size: 0.8em;
    margin-left: 5px;
  }

  .fa-2x {
    vertical-align: middle;
  }

  /* Adjustments for mobile screens */
  @media (max-width: 767px) {
    .navbar-nav {
      margin-top: 10px;
    }
  }
</style>

@section('content')
<!-- Main content -->
<section class="content-fluid">
  <div class="row">
    <div class="col-md-11 mx-auto">

      <nav class="navbar navbar-expand-lg navbar-light bg-light py-3">
        <!-- Left navbar links -->
        <div class="bg-light py-2 px-3 rounded">
          <ul class="navbar-nav mr-auto">
            @if(auth()->user()->hasRole('Super_Admin'))
            <li class="nav-item mr-3">
              <a class="nav-link text-secondary font-weight-bold" href="{{route('ticket.opentickets')}}" role="button"
                data-toggle="tooltip" title="Offen">
                <i class="far fa-folder-open fa-lg"></i>
                <span class="badge badge-info">{{ @$AllTicketsCount }}</span>
              </a>
            </li>
            <li class="nav-item mr-3">
              <a class="nav-link text-secondary font-weight-bold" href="{{route('ticket.unassigned')}}" role="button"
                data-toggle="tooltip" title="Nicht zugewiesen">
                <i class="fas fa-exclamation-triangle fa-lg"></i>
                <span class="badge badge-info">{{ @$UnassignedTicketsCount }}</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-secondary font-weight-bold" href="{{route('ticket.usertickets')}}" role="button"
                data-toggle="tooltip" title="Eigene Tickets">
                <i class="fas fa-clipboard-list fa-lg"></i>
                <span class="badge badge-info">{{ @$myTicketsCount }}</span>
              </a>
            </li>

            <li class="nav-item">
              <!-- Dropdown for selecting a city and downloading the PDF -->
              <div class="form-group">
                <select id="city-dropdown" class="form-control">
                  <option value="" selected disabled>Select a city</option>
                  @if(@$cityTicketCounts)
                  @foreach($cityTicketCounts as $cityName => $count)
                  <option value="{{ route('city.tickets.pdf', $cityName) }}">{{ $cityName }} ({{ $count }})</option>
                  @endforeach
                  @endif
                </select>
              </div>

            </li>

            @endif
          </ul>
        </div>
        <div class="vertical-divider"></div>
        <!-- Middle navbar -->
        <div class="bg-light py-2 px-3 rounded mx-4">
          <ul class="navbar-nav mx-auto">
            @if(@$cityTicketCounts)
            @foreach($cityTicketCounts as $cityName => $count)
            <!-- <li class="nav-item mx-2">
              <a class="nav-link text-secondary font-weight-bold" href="{{ route('city.tickets', $cityName) }}">
                {{ $cityName }}
                <span class="badge badge-success">{{ $count }}</span>
              </a>
            </li> -->
            <li class="nav-item mx-2">
              <a href="{{ route('city.tickets', $cityName) }}"
                class="font-weight-bold nav-link text-secondary city-name" data-city-name="{{ $cityName }}">
                {{ $cityName }} <span class="badge badge-success">{{ $count }}</span>
              </a>
            </li>
            @endforeach
            @endif
          </ul>
        </div>
        <!-- sidebar -->
        <div id="cityTicketsSidebar"
          style="display:none; width: 25%; height: 100%; position: fixed; z-index: 1; top: 0; right: 0; background-color: #fff; overflow-x: hidden; transition: 0.5s; padding-top: 60px;">
          <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
          <div id="cityTicketsDetails">
            {{ @$cityName }}
          </div>
        </div>
        <div class="vertical-divider"></div>
        <!-- Right navbar for admins -->
        <div class="bg-light py-2 px-3 rounded">
          <ul class="navbar-nav ml-auto">
            @foreach($admins as $admin)
            <li class="nav-item mx-2">
              <a class="nav-link text-secondary font-weight-bold" href="{{ route('userTicketsAdmins', $admin->id) }}">
                {{ $admin->name }}
                <span class="badge badge-secondary">{{ $ticketCounts[$admin->id] ?? 0 }}</span>
              </a>
            </li>
            @endforeach
          </ul>
        </div>
      </nav>
    </div>
    <!-- /.col -->
    <div class="col-md-11 mx-auto">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title text-bold">
            @if (URL::current() == route('ticket.opentickets'))
            Anzahl offener Tickets:{{@$AllTicketsCount}}</h3>
          @elseif (URL::current() == route('ticket.unassigned'))
          Anzahl Nicht zugewiesener Tickets: {{@$UnassignedTicketsCount}}</h3>
          @elseif (URL::current() == route('ticket.history'))
          Anzahl Erledigter Tickets: {{@$done}}</h3>
          @endif

        </div>
        <!-- /.card-header -->
        <div class="card-body p-1">
          <div class="mailbox-messages display nowrap" style="width: 100%;">
            @if($myTickets && count($myTickets) > 0)
            <table class="display responsive compact table-sm" id="ticket_table">
              <thead>
                <tr>
                  <th></th>
                  <th class="text-center"></th>
                  <th class="text-center">Erstellt von</th>
                  <th>Anfrage</th>
                  <th>Das Gerät</th>
                  <th>Tel</th>
                  <th>Standort</th>
                  <th>Priorität</th>
                  <th>Erstellt am</th>
                  <th class="none">Beschreibung</th>
                </tr>
              </thead>
              <tbody>
                @foreach($myTickets as $myTicket)
                <tr>
                  <td class="text-center">
                    @if($myTicket->ticket_status_id == 1)
                    <i class="fas fa-circle fa-1x" data-toggle="tooltip" title="Nicht begonnen"
                      style="color:#001B2E"></i>
                    @elseif($myTicket->ticket_status_id == 2)
                    <i class="fas fa-wrench fa-1x" data-toggle="tooltip" title="In Bearbeitung"
                      style="color:#3490DC"></i>
                    @elseif($myTicket->ticket_status_id == 3)
                    <i class="far fa-check-circle fa-1x" data-toggle="tooltip" title="Erledigt"
                      style="color:#285D17"></i>
                    @elseif($myTicket->ticket_status_id == 4)
                    <i class="fas fa-user-friends fa-1x" data-toggle="tooltip" title="Wartet auf jemand anderen"
                      style="color:#F9A620"></i>
                    @elseif($myTicket->ticket_status_id == 5)
                    <i class="fas fa-pause fa-1x" data-toggle="tooltip" title="Zurückgestellt"
                      style="color:#e3342f"></i>
                    @elseif($myTicket->ticket_status_id == 7)
                    <i class="fa-solid fa-message fa-1x" data-toggle="tooltip" title="Warten auf Antwort"
                      style="color:#c2410c"></i>
                    @else
                    <i class="far fa-copy fa-1x" data-toggle="tooltip" title="Duplikat" style="color:#285D17"></i>
                    @endif
                  </td>
                  <td class="col-md-1">
                    <select name="assignedTo" id="{{$myTicket->id}}" class="mailbox-star assignTo custom-select">
                      <option value="">Zuweisen</option>
                      @foreach($admins as $admin)
                      <option value="{{ $admin->id }}" {{$admin->id == $myTicket->assignedTo ? 'selected' : '' }}>{{
                        $admin->username }}</option>
                      @endforeach
                    </select>
                  </td>
                  <!-- <td class="text-left">
                    <span class="btn btn-success btn-small"><i class="fas fa-check"></i></span>
                  </td> -->

                  <td class="text-center"><a
                      href="{{url ('ticket/'.$myTicket->id)}}">{{@$myTicket->subUser->username}}</a></td>
                  <!-- <td><a>{{$myTicket->tel_number}}</a></td>
                  <td><a>{{$myTicket->custom_tel_number}}</a></td> -->
                  <td><a href="{{url ('ticket/'.$myTicket->id)}}">{{$myTicket->problem_type}}</a></td>

                  <td><b>{{@$myTicket->invitem->gname}} </b></td>
                  <td>{{@$myTicket->tel_number}}
                    @if(@$myTicket->custom_tel_number)
                    <i class="fas fa-grip-lines-vertical"></i> {{@$myTicket->custom_tel_number}}
                    @endif
                  </td>
                  <td>{{@$myTicket->subUser->ort}}</td>
                  <td>
                    @if($myTicket->priority_id == 1)
                    <!-- <i class="fas fa-circle" data-toggle="tooltip" title="bla bla" style="color:#3490dc"></i> -->
                    <span class="badge badge-pill badge-primary">Niedrig</span>

                    @elseif ($myTicket->priority_id == 2)
                    <!-- <i class="fas fa-circle " data-toggle="tooltip" title="bla bla" style="color:#285D17"></i> -->
                    <span class="badge badge-pill badge-success">Normal</span>
                    @else
                    <!-- <i class="fas fa-circle" data-toggle="tooltip" title="bla bla" style="color:#e3342f"></i> -->
                    <span class="badge badge-pill badge-danger">Hoch</span>
                    @endif
                  </td>
                  <td>{{@$myTicket->created_at->diffForHumans()}}
                    <p class="small muted">{{ @$myTicket->created_at->format('d.m.Y ')}}</p>
                  </td>
                  <td>{!!$myTicket->notizen!!}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            <img src="/images/admin_images/no_ticket.png" alt="why not">
            @endif
            <!-- /.table -->
          </div>
          <!-- /.mail-box-messages -->
        </div>



        <!-- /.card-body -->
        <div class="card-footer p-2">
          @if(@$city)
          <div class="row">
            <div class="col-md-12">
              <h5>{{ @$city->pnname }}</h5>
              <ol id="notesList" class="list-group list-group-flush">
                @foreach($city->notes as $note)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span class="note-content">{{ @$note->content }}</span>
                  <input type="text" class="form-control edit-note-input" value="{{ @$note->content }}"
                    data-note-id="{{ $note->id }}" style="display: none;">

                  <!-- Edit button -->
                  <button class="btn btn-outline-primary btn-sm edit-note-btn" data-note-id="{{ $note->id }}">
                    <i class="fas fa-pen"></i>
                  </button>

                  <!-- Save button -->
                  <button class="btn btn-outline-success btn-sm save-note-btn" data-note-id="{{ $note->id }}"
                    style="display: none;">
                    <i class="fas fa-save"></i>
                  </button>

                  <!-- Cancel button -->
                  <button class="btn btn-outline-secondary btn-sm cancel-note-btn" style="display: none;">
                    <i class="fas fa-times"></i>
                  </button>

                  <!-- Checkmark button (Delete) -->
                  <button class="btn btn-sm btn-outline-danger delete-note-btn" data-note-id="{{ $note->id }}"
                    style="display:none">
                    <i class="fas fa-trash"></i>
                  </button>
                </li>
                @endforeach
              </ol>
              <div class="row mb-3">
                <div class="col-md-12 d-flex">
                  <input type="text" id="newNoteContent" class="form-control mr-2" placeholder="neuer Hinweis?">
                  <button class="btn" id="addNoteBtn">
                    <i class="fas fa-plus text-success"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          @endif
        </div>


        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
  </div>
  <!-- /.row -->
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
    let hoverTimeout; // Variable to hold the timeout, ensuring we debounce hover events.

    // Mouse enter event for each city name element
    $('.city-name').mouseenter(function () {
      const cityName = $(this).data('city-name'); // Get the city name from the data attribute

      // Cancel any pending operations to close the nav or fetch data
      clearTimeout(hoverTimeout);

      // Set a new timeout to debounce the hover effect and fetch data for the hovered city
      hoverTimeout = setTimeout(function () {
        openNav(); // Open the sidebar
        fetchCityTickets(cityName); // Fetch and display tickets for the hovered city
      }, 200); // A short delay ensures we're not triggering too many requests rapidly
    });

    // Mouse leave event for each city name element
    $('.city-name').mouseleave(function () {
      // Start a timer to close the nav if the user doesn't hover over another city name or the sidebar itself
      hoverTimeout = setTimeout(function () {
        if (!$('#cityTicketsSidebar:hover').length) {
          closeNav(); // Close the sidebar if the cursor is not over it
        }
      }, 200);
    });

    // Event to handle mouse entering the sidebar, keeping it open
    $('#cityTicketsSidebar').mouseenter(function () {
      clearTimeout(hoverTimeout); // Cancel any pending close operations
    });

    // Event to handle mouse leaving the sidebar, closing it
    $('#cityTicketsSidebar').mouseleave(function () {
      closeNav(); // Close the sidebar
    });
  });

  // Function to open the sidebar
  function openNav() {
    $("#cityTicketsSidebar").stop().fadeIn(200); // Use fadeIn for smoother transitions
  }

  // Function to close the sidebar
  function closeNav() {
    $("#cityTicketsSidebar").stop().fadeOut(200); // Use fadeOut for smoother transitions
  }

  // Function to fetch and display city tickets
  function fetchCityTickets(cityName) {
    console.log("Fetching tickets for city:", cityName);
    $.ajax({
      url: '/getCityTicketsDetails/' + cityName,
      type: 'GET',
      success: function (response) {
        console.log(response);
        let ticketsHtml = '';
        if (response.tickets.length === 0 && response.notes.length === 0) {
          closeNav(); // Close the sidebar if no tickets or notes are found
          return;
        }

        // Set the city name as a title at the top of the sidebar
        ticketsHtml += '<div class="px-3 py-2 bg-gray-300">';
        ticketsHtml += '<h4 class="text-gray-700 font-bold">' + cityName + '</h4>';
        ticketsHtml += '</div>';

        // Tickets
        response.tickets.forEach(function (ticket) {
          ticketsHtml += '<div class="bg-gray-100 rounded overflow-hidden shadow-lg mb-2">';
          ticketsHtml += '<div class="px-3 py-2 bg-gray-200">';
          ticketsHtml += '<p class="text-gray-600 text-xs">Ersteller: <span class="text-blue-500 font-bold">' + ticket.sub_user.vorname + ' ' + ticket.sub_user.name + '</span></p>';
          ticketsHtml += '</div>';
          ticketsHtml += '<div class="p-3">';
          ticketsHtml += '<h5 class="font-bold text-sm mb-1">' + ticket.problem_type + '</h5>';
          ticketsHtml += '<div class="text-gray-700 text-xs leading-tight">' + ticket.notizen.replace(/\n/g, '<br>') + '</div>';
          if (ticket.admin_notes) {
            ticketsHtml += '<p class="text-gray-600 text-xs italic">' + ticket.admin_notes + '</p>';
          }
          ticketsHtml += '</div>';
          ticketsHtml += '</div>';
        });

        // Notes Card
        if (response.notes.length > 0) {
          let notesHtml = '<div class="bg-green-100 rounded overflow-hidden shadow-lg mb-2 mt-4">'; // Green background for notes card
          notesHtml += '<div class="px-3 py-2 bg-green-300">'; // Darker green header for contrast
          notesHtml += '<h4 class="font-bold text-green-800">Bedarf</h4>'; // Green title
          notesHtml += '</div>';
          notesHtml += '<div class="p-3">';
          notesHtml += '<ol class="list-decimal pl-5 text-sm text-gray-700">'; // Ordered list for notes
          response.notes.forEach(function (note) {
            notesHtml += '<li>' + note.content + '</li>';
          });
          notesHtml += '</ol>';
          notesHtml += '</div>';
          notesHtml += '</div>';
          ticketsHtml += notesHtml; // Append the notes card at the end
        }

        $('#cityTicketsDetails').html(ticketsHtml);
      },
      error: function (error) {
        console.error("Error fetching city tickets:", error);
        $('#cityTicketsDetails').html('<p>Error loading tickets for ' + cityName + '.</p>');
      }
    });
  }


  $(document).ready(function () {
    $('#ticket_table').DataTable().destroy();
    $('#ticket_table').DataTable({
      searching: false,
      paging: true,
      info: false,
      responsive: true,
      autoWidth: false,
      columnDefs: [
        { targets: 8, width: "1%" },
        { "orderable": false, "targets": 1 },
        { "orderable": false, "targets": 8 }
      ]
    });
  });

  $('.delete-note-btn').dblclick(function () {
    var noteId = $(this).data('note-id');
    deleteNote(noteId, $(this));
  });

  function deleteNote(noteId, $button) {
    $.ajax({
      url: '/notes/' + noteId,
      type: 'POST', // Change to POST
      data: { _method: 'DELETE' }, // Add this line
      success: function (response) {
        // Remove the note item from the list
        $button.closest('.list-group-item').remove();
      },
      error: function (error) {
        // Handle error
        console.error("Error deleting note: ", error);
      }
    });
  }

  // Edit button click event
  $('.edit-note-btn').click(function () {
    var $noteItem = $(this).closest('.list-group-item');

    $noteItem.find('.note-content, .edit-note-btn').hide();
    $noteItem.find('.edit-note-input, .save-note-btn, .cancel-note-btn, .delete-note-btn').show();
  });

  $('#addNoteBtn').click(function () {
    var newContent = $('#newNoteContent').val();
    if (newContent.trim() !== '') {
      // AJAX request to add the new note
      $.ajax({
        url: '/notes', // Your URL for adding new notes
        type: 'POST',
        data: {
          content: newContent,
          place_id: '{{ @$city->id }}' // Assuming you have the city ID available
        },
        success: function (response) {
          // Append the new note to the list and clear the input field
          $('#notesList').append('<li class="align-items-center d-flex justify-content-between list-group-item">' + newContent + ' </li>');
          $('#newNoteContent').val('');
        },
        error: function (error) {
          // Handle error
          console.error("Error adding note: ", error);
        }
      });
    }
  });

  function updateNote(noteId, newContent, $noteItem) { // Include $noteItem as a parameter
    $.ajax({
      url: '/notes/' + noteId,
      type: 'POST',
      data: {
        content: newContent,
        _method: 'PATCH'
      },
      success: function (response) {
        // Use $noteItem in the success callback
        $noteItem.find('.note-content').text(newContent).show();
        $noteItem.find('.edit-note-btn').show();
        $noteItem.find('.edit-note-input, .save-note-btn, .cancel-note-btn, .delete-note-btn').hide();
      },
      error: function (error) {
        console.error("Error updating note: ", error);
      }
    });
  }

  $('.save-note-btn').click(function () {
    var noteId = $(this).data('note-id');
    var newContent = $(this).siblings('.edit-note-input').val();
    var $noteItem = $(this).closest('.list-group-item');
    updateNote(noteId, newContent, $noteItem); // Pass $noteItem as an argument
  });


  // Cancel button click event
  $('.cancel-note-btn').click(function () {
    var $noteItem = $(this).closest('.list-group-item');

    $noteItem.find('.note-content, .edit-note-btn').show();
    $noteItem.find('.edit-note-input, .save-note-btn, .cancel-note-btn, .delete-note-btn').hide();
  });

  $(document).ready(function () {
    $(document).on('change', '.assignTo', function () {
      let ticket_id = $(this).attr('id')
      let assignedTo = $(this).val();
      $.ajax({
        type: "post",
        url: "{{route('ticket.assignedTo')}}",
        data: { "assignedTo": assignedTo, "ticket_id": ticket_id },
        success: function (result) {
        }
      });
    });
  })


    $(document).ready(function() {
    // Trigger download when a city is selected
    $('#city-dropdown').on('change', function() {
      var pdfUrl = $(this).val();  // Get the selected city's URL
      if (pdfUrl) {
        window.location.href = pdfUrl;  // Redirect to download the PDF
      }
    });
  });
</script>


@endsection