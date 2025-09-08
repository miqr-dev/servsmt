@extends('layouts.admin_layout.admin_layout')

@section('content')

@if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
@endif

<div class="container">
<h1 class="mb-3"><strong>Sek-Gruppen</strong></h1>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center">
      <a href="{{ route('korso.dashboard') }}" class="btn btn-secondary mr-3">
        Zurück
      </a>
    </div>
    <button class="btn btn-success" data-toggle="modal" data-target="#createSekGroupModal">
      + Neue Sek-Gruppe
    </button>
  </div>

  @if($groups->count())
  <table class="table table-bordered table-hover">
    <thead class="thead-light">
      <tr>
        <th>Name</th>
        <th>E-Mail</th>
        <th class="text-center">Mitglieder</th>
        <th class="text-right">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @foreach($groups as $group)
      <tr>
        <td>{{ $group->name }}</td>
        <td>{{ $group->email }}</td>
        <td class="text-center">
          <a href="{{ route('sek-groups.members.edit', $group) }}" class="btn btn-sm btn-primary">
            Verwalten ({{ $group->users()->count() }})
          </a>
        </td>
        <td class="text-right">
          <form action="{{ route('sek-groups.destroy', $group) }}" method="POST" class="d-inline-block"
            onsubmit="return confirm('Möchten Sie „{{ $group->name }}“ wirklich löschen?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">
              Löschen
            </button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Create Sek Group Modal -->
  <div class="modal fade" id="createSekGroupModal" tabindex="-1" role="dialog"
    aria-labelledby="createSekGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="{{ route('sek-groups.store') }}" method="POST" class="modal-content">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title" id="createSekGroupModalLabel">Neue Sek-Gruppe</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          {{-- Name --}}
          <div class="form-group">
            <label for="name">Gruppenname</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name') }}" placeholder="z. B. Sek Berlin">
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Email --}}
          <div class="form-group">
            <label for="email">Gruppen-E-Mail</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email') }}" placeholder="sekberlin@example.com">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Abbrechen
          </button>
          <button type="submit" class="btn btn-primary">
            Gruppe erstellen
          </button>
        </div>
      </form>
    </div>
  </div>

  {{ $groups->links() }}
  @else
  <div class="alert alert-info">
    Noch keine Sek-Gruppen vorhanden. <a href="{{ route('sek-groups.create') }}">Eine erstellen</a>.
  </div>
  @endif
</div>
@endsection

@section('script')
<script>
  $(function () {
    @if ($errors->has('name') || $errors->has('email'))
      $('#createSekGroupModal').modal('show');
    @endif
  });
</script>
@endsection
