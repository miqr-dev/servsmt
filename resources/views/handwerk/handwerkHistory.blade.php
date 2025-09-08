@extends('layouts.admin_layout.admin_layout')
<style>
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
</style>
@section('content')
<!-- Main content -->
@if(auth()->user()->hasAnyRole('Super_Admin','handwerk','handwerk_admin'))
<section class="content">
  <div class="row">
    <div class="col-md-3">
      <div class="card card-outline card-secondary">
        <div class="card-header">
          <h3 class="card-title">Handwerk Ordner</h3>

        </div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item active"><a href="{{route('ticket.usertickets')}}" class="nav-link"><i
                  class="fas fa-inbox"></i> Offene
                <span class="badge float-right"
                  style="background-color: #004873; color:white;">{{$myhandwerkTicketsCount}}</span></a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link"><i class="far fa-check-circle"></i> Erledigte <span
                  class="badge bg-success float-right">{{$handwerkticketsdoneCount}}</span></a></a>
            </li>
          </ul>
        </div>
        <!-- /.card-body -->
      </div>
    </div>
    <div class="col-md-9">
      <div class="card card-secondary card-outline">
        <div class="card-header">
          <h3 class="card-title">Anzahl eigene offener Tickets: <span class="text-bold"
              style="color:#004873">{{$myhandwerkTicketsCount}} </span> <span class="text-right">{{ $user->ort }} :<span class="text-bold"
              style="color:#004873"> {{$myhandwerkTicketsCountCity}}</span></span></h3> 
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
                  <th>Raum</th>
                  <th>Erledigt von</th>
                  <th>Erledigt am</th>
                  <th class="text-right">Erstellt am</th>
                  <th class="none">Beschreibung</th>
                </tr>
              </thead>
              <tbody>
                @forelse($handwerkticketsdone as $myHandwerkTicket)
                <tr>
                  <td></td>
                  <td><a href="{{url ('handwerk/'.$myHandwerkTicket->id)}}">{{ $myHandwerkTicket->problem_type}}</a></td>
                  <td>{{ $myHandwerkTicket->submitter_name}}</td>
                  <td>{{ $myHandwerkTicket->location->address }}</td>
                  <td>{{ @$myHandwerkTicket->room->rname }} {{ @$myHandwerkTicket->room->altrname }}</td>

                  <td>{{ @$myHandwerkTicket->done_by }}</td>
                  <td>{{ @$myHandwerkTicket->deleted_at }}</td>
                  <td class="text-right"><span class="d-none">{{$myHandwerkTicket->created_at->format('Ymd')}}</span>
                    {{$myHandwerkTicket->created_at->diffForHumans()}}</td>
                  <td>{!!$myHandwerkTicket->notizen!!}</td>
                </tr>
                @empty
                <td class="text-center">
                  <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
                </td>
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

    $('#userhandwerkticket_table').DataTable({
      searching: true,
      pageLength: 10,
      paging: true,
      info: true,
      responsive: true,
      autoWidth: true,
      order: [6, 'desc'],
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/German.json"
      },
      columnDefs: [
        { "orderable": false, "targets": 4 },
      ]
    });

  });

  function deleteConfirmation(id) {
    Swal.fire({
      title: 'sind Sie sicher ?',
      text: "Sie können dies nicht rückgängig machen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#661421',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ja, löschen !',
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
              console.log('its in else')
            }
          }
        });

      } else {
        e.dismiss;
      }

    }, function (dismiss) {
      return false;
    })
  }

</script>

@endsection