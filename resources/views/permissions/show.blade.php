@extends('layouts.admin_layout.admin_layout')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-6 mx-auto">
        <h2>{{ $permission->name }}</h2>
        <a href="{{ route('permissions.index') }}" class="btn btn-outline-back float-left mb-3" data-toggle="tooltip" data-placement="right" title="Zurück"><i class="fas fa-undo-alt"></i></a>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-6 mx-auto">
        <div class="form-group">
          <strong>Kategorie:</strong>
          <p>{{ optional($permission->category)->name ?? '—' }}</p>
        </div>
        <div class="form-group">
          <strong>Zugewiesen an Rollen:</strong>
          @forelse($permission->roles as $role)
            <h5 class="d-inline"><span class="badge badge-success">{{ $role->name }}</span></h5>
          @empty
            <p class="text-muted">Keine Rolle hat diese Permission.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
