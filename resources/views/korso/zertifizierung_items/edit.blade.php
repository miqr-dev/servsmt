@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container mt-4">
  <h3 class="text-success font-weight-bold mb-4">Bearbeiten</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <form action="{{ route('zertifizierung_items.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('korso.zertifizierung_items._form', ['buttonText' => 'Speichern'])
      </form>
    </div>
  </div>
</div>
@endsection
