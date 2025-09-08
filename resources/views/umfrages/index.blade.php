@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Evaluationen</h1>
    <a href="{{ route('umfrages.create') }}" class="btn btn-primary">+</a> <!-- Erstellen-Button -->
  </div>

  <select id="placeSelect" class="form-control mb-4">
    <option value="">Standort auswählen</option>
    @foreach ($places as $place)
    <option value="{{ $place->id }}">{{ $place->pnname }}</option>
    @endforeach
  </select>

  <div id="umfragesContainer">
    <!-- Die Umfragen werden hier basierend auf dem ausgewählten Ort geladen -->
  </div>
</div>


<!-- Umcategory Edit Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="editCategoryForm" method="POST">
      @csrf
      @method('PATCH')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCategoryModalLabel">Kategorie bearbeiten</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="category_name">Kategoriename</label>
            <input type="text" class="form-control" id="category_name" name="name" required>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <div>
            <button type="submit" class="btn btn-primary">Speichern</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
          </div>
          <div>
            <button type="button" class="btn btn-danger float-left" id="deleteCategoryBtn">Löschen</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function () {
    // Function to fetch umfrages
    function fetchUmfrages(placeId) {
      if (placeId) {
        $.ajax({
          url: '/umfrages/getByPlace/' + placeId,
          type: 'GET',
          success: function (data) {
            $('#umfragesContainer').html(data);
          }
        });
      } else {
        $('#umfragesContainer').empty();
      }
    }

    // When the place is changed, save the selection and fetch the corresponding data
    $('#placeSelect').change(function () {
      var placeId = $(this).val();
      localStorage.setItem('selectedPlace', placeId); // Save to localStorage
      fetchUmfrages(placeId);
    });

    // Check for a saved selection in localStorage when the page loads
    var selectedPlace = localStorage.getItem('selectedPlace');
    if (selectedPlace) {
      $('#placeSelect').val(selectedPlace); // Set the value without triggering change
      fetchUmfrages(selectedPlace); // Fetch umfrages directly
    }

  $('#deleteCategoryBtn').on('click', function () {
    if (confirm('Sind Sie sicher, dass Sie diese Kategorie löschen möchten?')) {
      var form = $('#editCategoryForm');
      form.attr('action', form.data('delete-url'));
      form.append('@method('delete ')');
      form.submit();
    }
  });

  $(document).on('click', '.edit-category-icon', function () {
    var categoryId = $(this).data('category-id');
    var categoryName = $(this).data('category-name');

    // Update the form action
    $('#editCategoryForm').attr('action', '/umcategories/' + categoryId);

    // Set the value of the category name input
    $('#category_name').val(categoryName);

    // Show the modal
    $('#editCategoryModal').modal('show');
  });
  });

</script>
@endsection