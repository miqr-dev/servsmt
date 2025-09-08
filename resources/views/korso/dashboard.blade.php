@extends('layouts.admin_layout.admin_layout')

<style>
  #ticketDetailSidebar {
    position: fixed;
    top: 0;
    right: -400px;
    /* Start hidden */
    width: 400px;
    height: 100vh;
    background-color: #f8f9fa;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    transition: right 0.3s ease;
    overflow-y: auto;
  }

  #ticketDetailSidebar.open {
    right: 0;
  }

  #ticketDetailSidebar .sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background-color: #65A30D;
    color: white;
    font-weight: bold;
  }

  #ticketDetailSidebar .sidebar-content {
    padding: 1rem;
  }

  #closeSidebar {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: white;
    cursor: pointer;
  }
</style>

@section('content')

<!-- Full Width Green Navbar -->
<nav class="navbar navbar-expand-lg w-100" style="background-color: #65A30D;">
  <div class="container-fluid">
    <a class="navbar-brand text-white font-weight-bold" href="#">Korso Dashboard</a>
  </div>
</nav>

<div class="container-fluid px-0">
  <div class="row no-gutters">

    <!-- Sidebar -->
    <div class="col-md-2 bg-white sidebar py-4 shadow-sm">
      <h5 class="text-center font-weight-bold text-dark">
        {{ Auth::user()->username }} <!-- Show logged-in user -->
      </h5>

      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="#" class="nav-link filter-tickets" data-filter="assigned">
            <i class="fas fa-user-check"></i> Eigene Tickets
            <span class="badge badge-primary ml-2" id="assigned-count">{{ $assignedCount }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link filter-tickets" data-filter="unassigned">
            <i class="fas fa-user-slash"></i> Nicht zugewiesene Tickets
            <span class="badge badge-primary ml-2" id="unassigned-count">{{ $unassignedCount }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link filter-tickets" data-filter="open">
            <i class="fas fa-hourglass-half"></i> Offene Tickets
            <span class="badge badge-primary ml-2" id="open-count">{{ $openCount }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link filter-tickets" data-filter="myDone">
            <i class="fas fa-check-double"></i> Erledigte
            <span class="badge badge-primary ml-2" id="myDone-count">{{ $myDoneCount }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link filter-tickets" data-filter="allDone">
            <i class="fas fa-check-circle"></i> Alle erledigten Tickets
            <span class="badge badge-primary ml-2" id="allDone-count">{{ $allDoneCount }}</span>
          </a>
        </li>
        <!-- Id 312 is Frau Dreyße -->
        @if(auth()->user()->id === 1 ||auth()->user()->id === 312)
        <li class="nav-item">
          <a href="{{ route('printmarketing.management') }}" class="nav-link">
            <i class="fa-solid fa-paint-roller"></i> Printmarketing Verwaltung
          </a>
        </li>
        @endif
      </ul>

      <!-- Refresh Button -->
      <div class="text-center my-4">
        <button class="btn btn-outline-success refresh-page">
          <i class="fas fa-sync-alt"></i> Refresh
        </button>
      </div>

      @if(auth()->user()->hasRole('Korso_Admin'))
      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="{{ route('onlinemarketing_items.index') }}" class="nav-link">
            <i class="fas fa-cogs"></i> Onlinemarketing Optionen
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('zertifizierung_items.index') }}" class="nav-link">
            <i class="fas fa-cogs"></i> Zert & Quali. Optionen
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('user.management') }}" class="nav-link">
            <i class="fas fa-cogs"></i> Rechtevergabe
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('sek-groups.index') }}" class="nav-link">
            <i class="fas fa-cogs"></i> Sek Groupen
          </a>
        </li>
        <!-- <li class="nav-item">
          <a href="#" class="nav-link filter-tickets" data-filter="allDone">
            <i class="fas fa-check-circle"></i> Alle erledigten Tickets
            <span class="badge badge-primary ml-2" id="allDone-count">{{ $allDoneCount }}</span>
          </a>
        </li> -->
      </ul>
      @endif

    </div>


    <!-- Main Content -->
    <div class="col-md-10">
      <div class="container mt-4">

        <!-- Flex container to align title & buttons in one row -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="text-dark font-weight-bold mb-0">Dashboard Overview</h2>

          @if(Auth::user()->hasRole('Korso_Admin'))
          <div class="d-flex flex-wrap">
            @foreach($korso_ma_users as $korso_ma)
            <button class="btn btn-outline-success btn-sm mx-1 korso-ma-filter" data-user-id="{{ $korso_ma->id }}">
              {{ strtoupper($korso_ma->vorname[0]) }}. {{ $korso_ma->name }}
              <span class="badge badge-success ml-1">{{ $korso_ma->assigned_tickets_count }}</span>
            </button>
            @endforeach
          </div>
          @endif
        </div>

        <!-- Tickets Table -->
        <div class="card mt-4 shadow">
          <div class="card-header text-white font-weight-bold" style="background-color: #65A30D;">
            Aktuelle Tickets
          </div>
          <div class="card-body">
            <table id="ticketsTable" class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Ersteller</th>
                  <th>Priorität</th>
                  <th>Bereich</th>
                  <th>Status</th>
                  <th>Zugewiesen an</th>
                  <th>Datum</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="ticketsBody">
                @include('korso.partials.tickets_table', ['tickets' => $tickets])
              </tbody>
            </table>
          </div>
        </div> <!-- End Table -->
      </div>
    </div>
  </div>
</div>

<!-- SideSlide -->
<div id="sidebarOverlay" class="d-none" style="
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.3);
  z-index: 9998;
"></div>
<!-- Slide-in Sidebar for Ticket Details -->
<div id="ticketDetailSidebar">
  <div class="sidebar-header">
    <span>Ticket Details</span>
    <button id="closeSidebar">&times;</button>
  </div>
  <div class="sidebar-content">
    <p>Loading...</p>
  </div>
</div>


@endsection

@section('script')
<script>


  $(document).ready(function () {
    let table = $('#ticketsTable').DataTable({
      language: {
        url: "https://cdn.datatables.net/plug-ins/1.10.18/i18n/German.json",
      },
      "paging": true,
      "searching": true,
      "ordering": true,
      "order": [[1, "asc"]], // Sort by priority (High first)
    });

    // Handle ticket filtering
    // Handle ticket filtering (Eigene, Nicht Zugewiesene, Offene, Erledigte)
    $('.filter-tickets').click(function (e) {
      e.preventDefault();
      let filter = $(this).data('filter');
      let filterName = "";

      // Remove highlight from Korso_Ma filters when switching to general filters
      $('.korso-ma-filter').removeClass('btn-success text-white').addClass('btn-outline-success');

      // Update the page title based on filter
      if (filter === "assigned") {
        filterName = "Eigene Tickets";
      } else if (filter === "unassigned") {
        filterName = "Nicht zugewiesene Tickets";
      } else if (filter === "open") {
        filterName = "Offene Tickets";
      } else if (filter === "myDone") {
        filterName = "Erledigte";
      } else if (filter === "allDone") {
        filterName = "Alle Erledigten";
      }

      $('h2').text(filterName); // Update the title

      // Remove active styles from all filters and highlight the clicked one
      $('.filter-tickets').removeClass('text-success font-weight-bold');
      $(this).addClass('text-success font-weight-bold');

      // Fetch filtered tickets
      $.ajax({
        url: "{{ route('dashboard.filterTickets') }}",
        type: "GET",
        data: { filter: filter },

        success: function (response) {
          table.clear().destroy(); // Destroy old DataTable
          $('#ticketsBody').html(response); // Update table body
          table = $('#ticketsTable').DataTable({
            language: {
              url: "https://cdn.datatables.net/plug-ins/1.10.18/i18n/German.json",
            },
            "paging": true,
            "searching": true,
            "ordering": true,
            "order": [[1, "asc"]], // Keep sorting by priority
          });
        },
        error: function () {
          alert('Error loading tickets.');
        }
      });
    });

    // Refresh Page on Button Click
    $('.refresh-page').click(function () {
      location.reload(); // Reload the page
    });

    // Handle Korso_Admin user filter
    $(document).on('click', '.korso-ma-filter', function () {
      let userId = $(this).data('user-id');
      let userName = $(this).text().trim(); // Get the button text (User Name)

      $('h2').text("Zugewiesene Tickets an " + userName); // Update title

      // Remove active styles and highlight selected user
      $('.korso-ma-filter').removeClass('btn-success text-white').addClass('btn-outline-success');
      $(this).removeClass('btn-outline-success').addClass('btn-success text-white');

      $.ajax({
        url: "{{ route('dashboard.filterTickets') }}",
        type: "GET",
        data: { user_id: userId },
        success: function (response) {
          table.clear().destroy(); // Destroy old DataTable
          $('#ticketsBody').html(response); // Update table body
          table = $('#ticketsTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "order": [[1, "asc"]], // Keep sorting by priority
          });
        },
        error: function () {
          alert('Error loading tickets.');
        }
      });
    });


    // Assign Ticket to User & Update UI Immediately
    $(document).on('change', '.assign-user', function () {
      let ticketId = $(this).data('id');
      let userId = $(this).val();

      $.ajax({
        url: "{{ route('korso.assign') }}",
        type: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          ticket_id: ticketId,
          user_id: userId
        },
        success: function (response) {
          // Change SweetAlert title based on whether a user is selected.
          let titleMessage = userId ? "Zugewiesen!" : "Nicht zugewiesen!";

          Swal.fire({
            icon: "success",
            title: titleMessage,
            text: response.message,
          });

          // Update UI dynamically without refresh.
          let assignedUserName = userId ? $('.assign-user option:selected').text() : "Nicht zugewiesen";
          $('#ticket-' + ticketId).find('.assigned-user').text(assignedUserName);
        },
        error: function () {
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Something went wrong.",
          });
        }
      });
    });

    $(document).on('click', '.mark-done', function () {
      let ticketId = $(this).data('id');

      Swal.fire({
        title: 'Bist du sicher?',
        text: "Dieses Ticket wird als erledigt markiert!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ja, erledigen!',
        cancelButtonText: 'Abbrechen'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "/korso/" + ticketId + "/done",
            type: "POST",
            data: {
              _token: "{{ csrf_token() }}"
            },
            success: function (response) {
              $('#ticket-' + ticketId).fadeOut();
              Swal.fire({
                icon: "success",
                title: "Erledigt!",
                text: "Ticket wurde als erledigt markiert.",
              });
            },
            error: function () {
              Swal.fire({
                icon: "error",
                title: "Fehler!",
                text: "Etwas ist schief gelaufen.",
              });
            }
          });
        }
      });
    });


    // SweetAlert Delete Ticket
    $(document).on('click', '.delete-ticket', function () {
      let ticketId = $(this).data('id');
      Swal.fire({
        title: 'Are you sure?',
        text: "This ticket will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '/korso/' + ticketId,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () {
              $('#ticket-' + ticketId).fadeOut();
              Swal.fire('Deleted!', 'Ticket has been deleted.', 'success');
            },
            error: function () {
              Swal.fire('Error!', 'Something went wrong.', 'error');
            }
          });
        }
      });
    });

    // View Details button click event
    $(document).on('click', '.view-details', function () {
      const ticketId = $(this).data('id');

      $.ajax({
        url: `/korso/${ticketId}/details`,
        type: 'GET',
        success: function (html) {
          $('#ticketDetailSidebar .sidebar-content').html(html);
          $('#ticketDetailSidebar').addClass('open');
          $('#sidebarOverlay').removeClass('d-none'); // show overlay
        },
        error: function () {
          alert('Fehler beim Laden der Ticketdetails.');
        }
      });
    });


    $(document).on('click', '#closeSidebar', function () {
      $('#ticketDetailSidebar').removeClass('open');
      $('#sidebarOverlay').addClass('d-none'); // hide overlay
    });

    // Close sidebar if clicked outside
    $(document).on('click', '#sidebarOverlay', function () {
      $('#ticketDetailSidebar').removeClass('open');
      $(this).addClass('d-none'); // hide overlay
    });

  });

</script>
@endsection