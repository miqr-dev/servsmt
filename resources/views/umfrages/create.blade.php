@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container">
    <h2>Neue Umfrage hinzufügen</h2>
    <a href="{{ route('umfrages.index') }}" class="btn btn-primary mb-3">Zurück</a>

    <form action="{{ route('umfrages.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="place_id">Standort</label>
            <select name="place_id" id="place_id" class="form-control" required>
                <option value="">Standort auswählen</option>
                @foreach ($places as $place)
                    <option value="{{ $place->id }}">{{ $place->pnname }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="title">Titel</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="url">URL</label>
            <input type="text" name="url" id="url" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="umcategory_id">Kategorie</label>
            <select name="umcategory_id" id="umcategory_id" class="form-control" required>
                <option value="">Kategorie auswählen</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <!-- Button to add a new category -->
            <button type="button" class="btn btn-link" id="addCategoryBtn">Neue Kategorie hinzufügen</button>
        </div>

        <button type="submit" class="btn btn-success">Umfrage hinzufügen</button>
    </form>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#addCategoryBtn').click(function() {
        var newCategory = prompt('Bitte geben Sie den Namen der neuen Kategorie ein:');
        if(newCategory) {
            $.ajax({
                url: '{{ route('umcategories.store') }}',
                type: 'POST',
                data: {
                    name: newCategory,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('#umcategory_id').append(new Option(data.name, data.id, true, true));
                }
            });
        }
    });
});
</script>
@endsection
