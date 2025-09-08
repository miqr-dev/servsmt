@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header', [
'title' => 'Benutzerverwaltung – Korso Rollen',
'colorClass' => 'ticket_header_korso',
'buttonClass' => 'btn-outline-green'
])

<div class="container mt-5">
  <div class="row">
    <!-- User Selection -->
    <div class="col-md-5">
      <h4>Benutzer auswählen und <span class="text-success">Korso_ma</span>-Rolle zuweisen</h4>
      <select id="user-select" class="form-control" multiple>
        <option value="">-- Benutzer auswählen --</option>
        @foreach($users as $user)
        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->vorname }})</option>
        @endforeach
      </select>
      <button id="assign-role" class="btn btn-success mt-2">
        <i class="fas fa-user-plus"></i> Rolle zuweisen
      </button>
    </div>

    <!-- Users with 'Korso_ma' Role -->
    <div class="col-md-7">
      <h4>Benutzer mit <span class="text-success">Korso_ma</span>-Rolle</h4>
      <ul class="list-group" id="korso-ma-users">
        @foreach($korsoMaUsers as $user)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>{{ $user->name }} ({{ $user->vorname }})</span>
          <form method="POST" action="{{ route('remove.role') }}" class="m-0 p-0">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <button type="submit" class="btn btn-danger btn-sm">
              <i class="fas fa-user-minus"></i> Entfernen
            </button>
          </form>
        </li>
        @endforeach
      </ul>
    </div>
  </div>
</div>
@endsection

@section('script')

<script>
  $(document).ready(function () {
    $('#user-select').select2({
      placeholder: "Benutzer suchen...",
      width: '100%'
    });

    $('#assign-role').click(function () {
      let userIds = $('#user-select').val(); // Array of selected user IDs
      if (userIds.length > 0) {
        $.post('{{ route("assign.role") }}', {
          _token: '{{ csrf_token() }}',
          user_ids: userIds
        }, function () {
          location.reload();
        });
      } else {
        alert('Bitte wählen Sie mindestens einen Benutzer aus.');
      }
    });
  });
</script>
@endsection