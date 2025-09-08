@extends('layouts.admin_layout.admin_layout')
<style>
  #userticket_table_korso .clickable-row-korso {
    cursor: pointer;
  }

  table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
  table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
    left: 10px !important;
    text-indent: 0 !important;
    background-color: #661421 !important;
  }

  /* prevent blinking tooltip */
  .tooltip {
    pointer-events: none;
  }

  /* datatable - hidden column wrapping */
  .dtr-data {
    white-space: normal
  }

  .nav-item.h_active {
    background-color: #004873;
    /* Use your preferred color */
    color: #fff;
    /* Text color, in this case white */
  }

  .nav-item.h_active .nav-link {
    color: #fff;
    /* Ensures the link color matches the text color */
  }
</style>
@section('content')
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-3">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title">Ordner</h3>

        </div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item active"><a href="{{route('ticket.usertickets')}}" class="nav-link"><i
                  class="fas fa-inbox"></i> Offene
                <span class="badge bg-primary float-right">{{$myTicketsCount}}</span></a>
            </li>
            <li class="nav-item">
              <a href="{{route('ticket.userticketsdone')}}" class="nav-link"><i class="far fa-check-circle"></i>
                Erledigte <span class="badge bg-success float-right">{{$ticketsdone}}</span></a>
            </li>
          </ul>
        </div>
        <!-- /.card-body -->
      </div>
    </div>
    <!-- /.col -->
    <div class="col-md-9">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">Anzahl offener Tickets: {{$myTicketsCount}} </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body p-1">
          <div class="mailbox-messages display nowrap" style="width: 100%;">
            <table class="display responsive compact table-sm" id="userticket_table">
              <thead>
                <tr>
                  <th></th>
                  <th></th>
                  <th class="text-center">Status</th>
                  @if(auth()->user()->hasRole('Super_Admin'))
                  <th>Erstellt von</th>
                  @else
                  <th>Zugewiesen an</th>
                  @endif
                  <th>Anfrage</th>
                  <th>Das Gerät</th>
                  <th>Priorität</th>
                  <th class="text-right">Erstellt am</th>
                  <th class="none">Beschreibung</th>
                </tr>
              </thead>
              <tbody>
                @forelse($myTickets as $myTicket)
                <tr>
                  <td></td>
                  <td>
                    @if($myTicket->id)
                    <button class="btn show_confirm" data-id="{{ $myTicket->id }}"
                      data-action="{{route('ticket.forceDelete', $myTicket->id)}}"
                      onclick="deleteConfirmation({{ $myTicket->id }})">
                      <i class="fa-regular fa-trash-can" style="color:#e3342f;"></i>
                    </button>
                    @endif
                  </td>

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
                    @else
                    <i class="far fa-copy fa-1x" data-toggle="tooltip" title="Duplikat" style="color:#285D17"></i>
                    @endif
                  </td>
                  @if(auth()->user()->hasRole('Super_Admin'))
                  <td><a href="{{url('ticket/'.$myTicket->id)}}">{{@$myTicket->subUser->username}}</a></td>
                  @else
                  <td><a href="{{url('ticket/'.$myTicket->id)}}">{{@$myTicket->user->username}}</a></td>
                  @endif
                  <td><a href="{{url('ticket/'.$myTicket->id)}}">{{$myTicket->problem_type}}</a></td>
                  <td><b>{{@$myTicket->invitem->gname}}</b></td>
                  <td>
                    @if($myTicket->priority_id == 1)
                    <span class="badge badge-pill badge-primary">Niedrig</span>
                    @elseif ($myTicket->priority_id == 2)
                    <span class="badge badge-pill badge-success">Normal</span>
                    @else
                    <span class="badge badge-pill badge-danger">Hoch</span>
                    @endif
                  </td>
                  <td class="text-right">{{$myTicket->created_at->diffForHumans()}}</td>
                  <td>{!!$myTicket->notizen!!}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center">
                    <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer p-0">
          <div class="mailbox-controls">
            <div class="float-right">
              <div class="btn-group">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@if(auth()->user()->hasAnyRole('Super_Admin','handwerk','handwerk_admin','Verwaltung'))
<section class="content">
  <div class="row">
    <div class="col-md-3">
      <div class="card card-outline card-secondary">
        <div class="card-header">
          <h3 class="card-title">Handwerk Ordner</h3>

        </div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item active">
              <a href="{{route('ticket.usertickets')}}" class="nav-link">
                <i class="fas fa-inbox"></i> Offene
                <span class="badge float-right"
                  style="background-color: #004873; color:white;">{{@$myhandwerkTicketsCount}}</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('handwerk.userhandwerkticketsdone') }}" class="nav-link">
                <i class="far fa-check-circle"></i> Erledigte
                <span class="badge bg-success float-right">{{@$handwerkticketsdone}}</span>
              </a>
            </li>
            @if(isset($userCities[$user->id]))
            @foreach(@$userCities[$user->id] as $city)
            <li
              class="nav-item {{ url()->current() == route('ticket.usertickets', ['city' => $city]) ? 'h_active' : '' }}">
              <a href="{{route('ticket.usertickets', ['city' => $city])}}" class="nav-link">
                {{ ucfirst($city) }}
                @if(isset($cityHandwerkCounts[$city]))
                <span class="badge float-right"
                  style="background-color: #004873; color:white;">{{@$cityHandwerkCounts[$city]}}</span>
                @endif
              </a>
            </li>
            @endforeach
            @endif
          </ul>
        </div>
        <!-- /.card-body -->
      </div>
    </div>
    <div class="col-md-9">
      <div class="card card-secondary card-outline">
        <div class="card-header">
          <h3 class="card-title">
            Anzahl eigene offener Tickets:
            <span class="text-bold" style="color:#004873">{{$myhandwerkTicketsCount}}</span>
            @if(request()->city)
            <span class="text-right text-capitalize">{{ request()->city }} :
              <span class="text-bold" style="color:#004873"> {{@$myhandwerkTicketsCountCity}}</span>
            </span>
            @else
            <span class="text-right">{{ ucfirst($user->ort) }} :
              <span class="text-bold" style="color:#004873"> {{@$myhandwerkTicketsCountCity}}</span>
            </span>
            @endif
          </h3>
          @if(auth()->user()->hasRole('handwerk_admin') || auth()->user()->hasRole('Super_Admin') ||
          auth()->user()->hasRole('Sekretariat'))
          <div class="text-right mb-3">
            <a href="{{ route('handwerk.city.openTicketsPDF', ['city' => $user->ort]) }}" class="btn btn-danger">
              <i class="fas fa-file-pdf"></i> PDF Herunterladen
            </a>
          </div>
          @endif
        </div>
        <div class="card-body p-1">
          <div class="mailbox-messages display nowrap" style="width: 100%;">
            <table class="display responsive compact table-sm" id="userhandwerkticket_table">
              <thead>
                <tr>
                  <th></th>
                  <th>Anfrage</th>
                  <th>Ersteller</th>
                  <th>Standort</th>
                  <th>Adresse</th>
                  <th>Raum</th>
                  <th>Priorität</th>
                  <th class="text-right">Erstellt am</th>
                  <th class="none">Beschreibung</th>
                </tr>
              </thead>
              <tbody>
                @forelse(@$myHandwerkTickets as $myHandwerkTicket)
                <tr>
                  <td></td>
                  <td><a href="{{url('handwerk/'.$myHandwerkTicket->id)}}">{{ @$myHandwerkTicket->problem_type}}</a>
                  </td>
                  <td>{{ @$myHandwerkTicket->submitter_name}}</td>
                  <td>{{ @$myHandwerkTicket->submitter_standort}}</td>
                  <td>{{ @$myHandwerkTicket->location->address }}</td>
                  <td>{{ @$myHandwerkTicket->room->rname }} {{ @$myHandwerkTicket->room->altrname }}</td>
                  <td>
                    @if($myHandwerkTicket->priority == 1)
                    <span class="badge badge-pill badge-primary">Niedrig</span>
                    @elseif ($myHandwerkTicket->priority == 2)
                    <span class="badge badge-pill badge-success">Normal</span>
                    @else
                    <span class="badge badge-pill badge-danger">Hoch</span>
                    @endif
                  </td>
                  <td class="text-right"><span class="d-none">{{@$myHandwerkTicket->created_at->format('Ymd')}}</span>
                    {{@$myHandwerkTicket->created_at->diffForHumans()}}</td>
                  <td>{!! @$myHandwerkTicket->notizen !!}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center">
                    <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer p-0">
          <div class="mailbox-controls">
            <div class="float-right">
              <div class="btn-group">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endcan
@if(auth()->user()->hasRole('Verwaltung'))
<section class="content">
  <div class="row">
    <div class="col-md-3">
      <div class="card card-outline card-success">
        <div class="card-header">
          <h3 class="card-title">Ordner</h3>

        </div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item active"><a href="{{route('ticket.usertickets')}}" class="nav-link"><i
                  class="fas fa-inbox"></i> Offene
                <span class="badge bg-success float-right">{{$assignedCount}}</span></a>
            </li>
            <li class="nav-item">
              <a href="{{route('ticket.userticketsdone')}}" class="nav-link"><i class="far fa-check-circle"></i>
                Erledigte <span class="badge bg-primary float-right">{{$myDoneCount}}</span></a>
            </li>
          </ul>
        </div>
        <!-- /.card-body -->
      </div>
    </div>
    <!-- /.col -->
    <div class="col-md-9">
      <div class="card card-success card-outline">
        <div class="card-header">
          <h3 class="card-title">Anzahl offener Tickets: {{$myTicketsCount}} </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body p-1">
          <div class="mailbox-messages display nowrap" style="width: 100%;">
            <table class="display responsive compact table-sm" id="userticket_table_korso">
              <thead>
                <tr>
                  <th class="text-center">Ersteller</th>
                  <th class="text-center">Status</th>
                  <th>Zugewiesen an</th>
                  <th>Anfrage</th>
                  <th>Priorität</th>
                  <th class="text-right">Erstellt am</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($korsoTicket as $myTicket)
                <tr class="clickable-row-korso" data-href="{{ url('/korso/' . $myTicket->id) }}">
                  <td>{{$myTicket->submitter_name ?? 'nicht zugewiesen'}}
                  </td>
                  <td class="text-center">
                    @if($myTicket->ticket_status)
                    @php
                    $statusColors = [
                    1 => 'secondary', // Nicht begonnen
                    2 => 'warning', // In Bearbeitung
                    3 => 'success', // Erledigt
                    4 => 'info', // Wartet auf jemand anderen
                    5 => 'dark', // Zurückgestellt
                    6 => 'danger', // Duplikat
                    7 => 'primary', // Warten auf Antwort
                    ];
                    $badgeClass = $statusColors[$myTicket->ticket_status->id] ?? 'light';
                    @endphp
                    <span class="badge badge-{{ $badgeClass }}">{{ $myTicket->ticket_status->name }}</span>
                    @else
                    <span class="badge badge-secondary">nicht zugewiesen</span>
                    @endif
                  </td>
                  <td>{{$myTicket->assignedTo ?? 'nicht zugewiesen'}}
                  </td>
                  <td>{{$myTicket->problem_type}}</td>
                  <td>
                    @if($myTicket->priority == 3)
                    <span class="badge badge-danger">Hoch</span>
                    @elseif($myTicket->priority == 2)
                    <span class="badge badge-warning">Normal</span>
                    @else
                    <span class="badge badge-secondary">Niedrig</span>
                    @endif
                  </td>
                  <td class="text-right">{{$myTicket->created_at->diffForHumans()}}</td>
                  <td>
                    @if($myTicket->id)
                    <button class="btn delete_korso_confirm" data-id="{{ $myTicket->id }}"
                      data-action="{{ route('korso.forceDelete', $myTicket->id) }}">
                      <i class="fa-regular fa-trash-can" style="color:#e3342f;"></i>
                    </button>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center">
                    <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer p-0">
          <div class="mailbox-controls">
            <div class="float-right">
              <div class="btn-group">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endcan

@endsection
@section('script')
<script>
  $(document).ready(function () {
    // Initialize DataTables if there are rows in the table
    if ($('#userticket_table tbody tr').length > 0) {
      try {
        $('#userticket_table').DataTable({
          searching: false,
          paging: false,
          info: false,
          responsive: true,
          autoWidth: false,
          columnDefs: [
            { targets: 6, width: "1%" },
          ]
        });
      } catch (error) {
        console.error("Error initializing DataTables: ", error);
      }
    }

    if ($('#userhandwerkticket_table tbody tr').length > 0) {
      try {
        $('#userhandwerkticket_table').DataTable({
          searching: true,
          pageLength: 10,
          paging: true,
          info: true,
          responsive: true,
          autoWidth: true,
          order: [7, 'desc'],
          "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/German.json"
          },
          columnDefs: [
            { "orderable": false, "targets": 8 },
          ]
        });
      } catch (error) {
        console.error("Error initializing DataTables: ", error);
      }
    }
    if ($('#userticket_table_korso tbody tr').length > 0) {
      try {
        $('#userticket_table_korso').DataTable({
          searching: true,
          pageLength: 10,
          paging: true,
          info: true,
          responsive: true,
          autoWidth: true,
          order: [7, 'desc'],
          "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/German.json"
          },
          columnDefs: [
            { "orderable": false, "targets": 8 },
          ]
        });
      } catch (error) {
        console.error("Error initializing DataTables: ", error);
      }
    }
  });
  $('#userticket_table_korso tbody').on('click', 'tr.clickable-row-korso', function (e) {
    if (!$(e.target).closest('a, button, i').length) {
      window.location = $(this).data('href');
    }
  });

  function deleteConfirmation(id) {
    if (!id) {
      console.error("Invalid ticket ID: ", id);
      return;
    }

    Swal.fire({
      title: 'sind Sie sicher ?',
      text: "Sie können dies nicht rückgängig machen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#661421',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ja, Weg !',
      cancelButtonText: 'Nein !'
    }).then(function (e) {
      if (e.value === true) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
          type: 'POST',
          url: "{{url('/ticket.force_delete')}}/" + id,
          data: { _token: CSRF_TOKEN },
          success: function (results) {
            console.log(results);
            if (results === 'true') {
              location.reload();
            } else {
              console.log('its in else');
            }
          },
          error: function (xhr, status, error) {
            console.error("Error deleting ticket: ", status, error);
          }
        });
      } else {
        e.dismiss;
      }
    }, function (dismiss) {
      return false;
    });
  }

  $(document).on('click', '.delete_korso_confirm', function (e) {
    e.stopPropagation(); // avoid triggering row click
    let id = $(this).data('id');
    let actionUrl = $(this).data('action');

    if (!id || !actionUrl) {
      console.error("Missing ID or URL for Korso delete");
      return;
    }

    Swal.fire({
      title: 'Bist du sicher?',
      text: "Das Löschen eines Korso-Tickets kann nicht rückgängig gemacht werden!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#661421',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ja, löschen!',
      cancelButtonText: 'Nein, abbrechen!'
    }).then((result) => {
      if (result.isConfirmed) {
        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
          type: 'POST',
          url: actionUrl,
          data: { _token: CSRF_TOKEN },
          success: function (response) {
            if (response === 'true') {
              Swal.fire('Gelöscht!', 'Das Ticket wurde dauerhaft gelöscht.', 'success')
                .then(() => location.reload());
            } else {
              Swal.fire('Fehler', 'Das Ticket konnte nicht gelöscht werden.', 'error');
            }
          },
          error: function () {
            Swal.fire('Fehler', 'Es ist ein Fehler aufgetreten.', 'error');
          }
        });
      }
    });
  });

</script>

@endsection