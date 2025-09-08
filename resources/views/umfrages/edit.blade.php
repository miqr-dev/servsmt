@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container">
  <h2>Linke bearbeiten</h2>
  <a href="{{ route('umfrages.index') }}" class="btn btn-primary mb-3">Zurück</a>

  <form action="{{ route('umfrages.update', $umfrage->id) }}" method="POST">
    @csrf
    @method('PUT') <!-- This is necessary to simulate a PUT request -->

    <div class="form-group">
      <label for="place_id">Standort</label>
      <select name="place_id" id="place_id" class="form-control" required>
        <option value="">Standort auswählen</option>
        @foreach ($places as $place)
        <option value="{{ $place->id }}" {{ $umfrage->place_id == $place->id ? 'selected' : '' }}>
          {{ $place->pnname }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="title">Titel</label>
      <input type="text" name="title" id="title" value="{{ $umfrage->title }}" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="url">URL</label>
      <input type="text" name="url" id="url" value="{{ $umfrage->url }}" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="umcategory_id">Kategorie</label>
      <select name="umcategory_id" id="umcategory_id" class="form-control" required>
        <option value="">Kategorie auswählen</option>
        @foreach ($categories as $category)
        <option value="{{ $category->id }}" {{ $umfrage->umcategory_id == $category->id ? 'selected' : '' }}>
          {{ $category->name }}
        </option>
        @endforeach
      </select>
      <!-- Button to add a new category -->
      <button type="button" class="btn btn-link" id="addCategoryBtn">Neue Kategorie hinzufügen</button>
    </div>

    <div class="d-flex justify-content-between">
      <button type="submit" class="btn btn-success">Änderungen speichern</button>
      
      <form action="{{ route('umfrages.destroy', $umfrage->id) }}" method="POST" onsubmit="return confirm('Möchten Sie diese Umfrage wirklich löschen?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Löschen</button>
      </form>
    </div>
  </form>
</div>
@endsection

<!-- Include the script section from your create view if necessary -->