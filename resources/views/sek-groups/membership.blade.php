@extends('layouts.admin_layout.admin_layout')

@section('content')
  <div class="container mt-5">
    <div class="row">
      {{-- ─────── Left: pick users to add ─────── --}}
      <div class="col-md-5">
        <h4>Benutzer auswählen und Mitglied hinzufügen</h4>
        <select id="user-select" class="form-control" multiple>
          <option value="">-- Benutzer auswählen --</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}">
              {{ $user->name }} ({{ $user->email }})
            </option>
          @endforeach
        </select>
        <button id="assign-members" class="btn btn-success mt-2">
          <i class="fas fa-user-plus"></i> Hinzufügen
        </button>
      </div>

      {{-- ─────── Right: current members ─────── --}}
      <div class="col-md-7">
        <h4>Mitglieder von {{ $group->name }}</h4>
        <ul class="list-group" id="group-users">
          @foreach($group->users as $member)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>{{ $member->name }} ({{ $member->email }})</span>

              <form method="POST"
                    action="{{ route('sek-groups.members.remove', $group) }}"
                    class="m-0 p-0">
                @csrf
                <input type="hidden" name="user_id" value="{{ $member->id }}">
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
      // turn that <select> into a searchable multi-picker
      $('#user-select').select2({
        placeholder: "Benutzer suchen...",
        width: '100%'
      });

      // assign selected users via AJAX, then reload
      $('#assign-members').click(function () {
        let userIds = $('#user-select').val(); // array of IDs
        if (!userIds || !userIds.length) {
          return alert('Bitte wählen Sie mindestens einen Benutzer aus.');
        }
        $.post(
          '{{ route("sek-groups.members.add", $group) }}',
          {
            _token: '{{ csrf_token() }}',
            user_ids: userIds
          },
          function () {
            location.reload();
          }
        );
      });
    });
  </script>
@endsection
