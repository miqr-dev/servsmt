@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container mt-4">
  <h3 class="text-success font-weight-bold mb-4">Zert & Quali. hinzufügen</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <form action="{{ route('zertifizierung_items.store') }}" method="POST">
        @csrf
        @include('korso.zertifizierung_items._form', ['buttonText' => 'Erstellen'])
      </form>
    </div>
  </div>
</div>
@endsection