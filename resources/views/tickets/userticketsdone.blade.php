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
<section class="content">
  <div class="row">
    <div class="col-md-3">
      <div class="card">
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
                Erledigte <span class="badge bg-success float-right">{{$ticketsdone}}</span></a></a>
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
          <h3 class="card-title">Anzahl erledigte Tickets: {{$ticketsdone}} <i class="fas fa-history"></i></h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body p-0">
          <div class="table-responsive mailbox-messages">
            <table class="table table-hover table-striped" id="ticket_table_done">
              <thead>
                <tr>
                  <th></th>
                  <th>Anfrage</th>
                  @if( auth()->user()->hasRole('Super_Admin'))
                  <th>Erstellet von</th>
                  @else
                  <th>Erledigt von</th>
                  @endif
                  <th>Erledigt am</th>
                  <th>Seit</th>
                  <th>Zugewiesen an</th>
                  <th class="none">Beschreibung</th>
                </tr>
              </thead>
              <tbody>
                @forelse($oldTickets as $myTicket)
                <tr>
                  <td></td>
                  <td class="mailbox-name"><a href="{{url ('ticket/'.$myTicket->id)}}">{{$myTicket->problem_type}}</a>
                  </td>
                  @if( auth()->user()->hasRole('Super_Admin'))
                  <td><b>{{@$myTicket->subUser->username}}</b></td>
                  @else
                  <td><b>{{@$myTicket->done_by}} </b></td>
                  @endif
                  <td><b>{{ optional($myTicket->deleted_at)->format('d.m.Y') }}</b></td>
                  <td>{{$myTicket->updated_at->diffForHumans()}}</td>
                  <td><b>{{@$myTicket->user->username}}</b></td>
                  <td>{!!$myTicket->notizen!!}</td>
                </tr>
                @empty
                <td class="text-center">
                  <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
                </td>
                @endforelse
              </tbody>
            </table>
            <!-- /.table -->
          </div>
          <!-- /.mail-box-messages -->
        </div>
        <!-- /.card-body -->
        <div class="card-footer p-0">
          <div class="mailbox-controls">
            <div class="float-right">
              <div class="btn-group">
                <!-- <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-left"></i></button>
                  <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-right"></i></button> -->
              </div>
              <!-- /.btn-group -->
            </div>
            <!-- /.float-right -->
          </div>
        </div>
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
</section>

<section class="content">
  <div class="row">
    <div class="col-md-3">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Ordner</h3>

        </div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item active"><a href="{{route('ticket.usertickets')}}" class="nav-link"><i
                  class="fas fa-inbox"></i> Offene
                <span class="badge bg-primary float-right">{{$assignedCount}}</span></a>
            </li>
            <li class="nav-item">
              <a href="{{route('ticket.userticketsdone')}}" class="nav-link"><i class="far fa-check-circle"></i>
                Erledigte <span class="badge bg-success float-right">{{$myDoneCount}}</span></a></a>
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
          <h3 class="card-title">Anzahl erledigte Tickets: {{$ticketsdone}} <i class="fas fa-history"></i></h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body p-0">
          <div class="table-responsive mailbox-messages">
            <table class="table table-hover table-striped" id="ticket_table_done">
              <thead>
                <tr>
                  <th></th>
                  <th>Anfrage</th>
                  @if( auth()->user()->hasRole('Super_Admin'))
                  <th>Erstellet von</th>
                  @else
                  <th>Erledigt von</th>
                  @endif
                  <th>Erledigt am</th>
                  <th>Seit</th>
                  <th>Zugewiesen an</th>
                  <th class="none">Beschreibung</th>
                </tr>
              </thead>
              <tbody>
                @forelse($oldKorsoTickets as $myTicket)
                <tr>
                  <td></td>
                  <td class="mailbox-name"><a href="{{url ('korso/'.$myTicket->id)}}">{{$myTicket->problem_type}}</a>
                  </td>
                  @if( auth()->user()->hasRole('Super_Admin'))
                  <td><b>{{@$myTicket->submitter->username}}</b></td>
                  @else
                  <td><b>{{@$myTicket->doneByUser->username}} </b></td>
                  @endif
                  <td><b>{{ optional($myTicket->deleted_at)->format('d.m.Y') }}</b></td>
                  <td>{{$myTicket->updated_at->diffForHumans()}}</td>
                  <td><b>{{@$myTicket->assignedUser->username ?? 'nicht zugewiesen'}}</b></td>
                  <td>{!!$myTicket->notizen!!}</td>
                </tr>
                @empty
                <td class="text-center">
                  <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
                </td>
                @endforelse
              </tbody>
            </table>
            <!-- /.table -->
          </div>
          <!-- /.mail-box-messages -->
        </div>
        <!-- /.card-body -->
        <div class="card-footer p-0">
          <div class="mailbox-controls">
            <div class="float-right">
              <div class="btn-group">
                <!-- <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-left"></i></button>
                  <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-right"></i></button> -->
              </div>
              <!-- /.btn-group -->
            </div>
            <!-- /.float-right -->
          </div>
        </div>
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
</section>


@endsection
@section('script')
<script>
  $(document).ready(function () {
    $('#ticket_table_done').DataTable({
      searching: false,
      paging: false,
      info: false,
      responsive: true,
      autoWidth: false,
      columnDefs: [
        { targets: 6, width: "1%" },
      ]
    });
  });



</script>

@endsection