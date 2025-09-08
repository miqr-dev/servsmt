@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container mt-4">
  <h3 class="text-success font-weight-bold mb-4">Neue Onlinemarketing Option hinzufügen</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <form action="{{ route('onlinemarketing_items.store') }}" method="POST">
        @csrf
        @include('korso.onlinemarketing_items._form', ['buttonText' => 'Erstellen'])
      </form>
    </div>
  </div>
</div>
@endsection