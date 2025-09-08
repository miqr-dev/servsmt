@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-success font-weight-bold">Onlinemarketing Optionen verwalten</h3>
    <a href="{{ route('onlinemarketing_items.create') }}" class="btn btn-success">
      <i class="fas fa-plus-circle"></i> Neue Option hinzufügen
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 80%;">Bezeichnung</th>
            <th class="text-center">Aktionen</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $item)
          <tr>
            <td>{{ $item->name }}</td>
            <td class="text-center">
              <a href="{{ route('onlinemarketing_items.edit', $item->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-edit"></i> Bearbeiten
              </a>
              <form action="{{ route('onlinemarketing_items.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Möchten Sie diese Option wirklich löschen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash-alt"></i> Löschen
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="2" class="text-center text-muted">Keine Optionen gefunden.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
