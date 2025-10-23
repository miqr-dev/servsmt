@extends('layouts.admin_layout.admin_layout')
<style>
  #custom_ticket_table .clickable-row {
    cursor: pointer;
  }
</style>
@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Custom Ticket View</h3>
      </div>
      <div class="card-body p-1">
        <div class="table-responsive">
          <table class="table table-hover table-striped" id="custom_ticket_table">
            <thead>
              <tr>
                <th class="text-center">Status</th>
                <th>Erstellt von</th>
                <th>Anfrage</th>
                <th>Das Gerät</th>
                <th>Priorität</th>
                <th class="text-right">Erstellt am</th>
                <th>Beschreibung</th>
              </tr>
            </thead>
            <tbody>
              @forelse($tickets as $ticket)
              <tr class='clickable-row' data-href="{{url('ticket/'.$ticket->id)}}">
                <td class="text-center">
                  @if($ticket->ticket_status_id == 1)
                  <i class="fas fa-circle fa-1x" data-toggle="tooltip" title="Nicht begonnen"
                    style="color:#001B2E"></i>
                  @elseif($ticket->ticket_status_id == 2)
                  <i class="fas fa-wrench fa-1x" data-toggle="tooltip" title="In Bearbeitung"
                    style="color:#3490DC"></i>
                  @elseif($ticket->ticket_status_id == 3)
                  <i class="far fa-check-circle fa-1x" data-toggle="tooltip" title="Erledigt"
                    style="color:#285D17"></i>
                  @elseif($ticket->ticket_status_id == 4)
                  <i class="fas fa-user-friends fa-1x" data-toggle="tooltip" title="Wartet auf jemand anderen"
                    style="color:#F9A620"></i>
                  @elseif($ticket->ticket_status_id == 5)
                  <i class="fas fa-pause fa-1x" data-toggle="tooltip" title="Zurückgestellt"
                    style="color:#e3342f"></i>
                  @elseif($ticket->ticket_status_id == 7)
                  <i class="fa-solid fa-message fa-1x" data-toggle="tooltip" title="Warten auf Antwort"
                    style="color:#c2410c"></i>
                  @else
                  <i class="far fa-copy fa-1x" data-toggle="tooltip" title="Duplikat" style="color:#285D17"></i>
                  @endif
                </td>
                <td>{{@$ticket->subUser->username}}</td>
                <td>{{$ticket->problem_type}}</td>
                <td><b>{{@$ticket->invitem->gname}}</b></td>
                <td>
                  @if($ticket->priority_id == 1)
                  <span class="badge badge-pill badge-primary">Niedrig</span>
                  @elseif ($ticket->priority_id == 2)
                  <span class="badge badge-pill badge-success">Normal</span>
                  @else
                  <span class="badge badge-pill badge-danger">Hoch</span>
                  @endif
                </td>
                <td class="text-right">{{$ticket->created_at->diffForHumans()}}</td>
                <td>{!!$ticket->notizen!!}</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center">
                  <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
@section('script')
<script>
  jQuery(document).ready(function($) {
    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });
  });
</script>
@endsection