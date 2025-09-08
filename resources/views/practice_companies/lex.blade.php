@extends('layouts.admin_layout.admin_layout')

@section('content')

<div class="container">
  <h1 class="mt-5 text-center" style="font-size:2.5rem; font-weight: bold;">Lexware-Datenverwaltung für Übungsfirmen in
    {{ $place->pnname }}</h1>


  @if($isSuperAdmin)
  <div class="d-flex justify-content-end mb-3">

  </div>
  @endif

  <div class="container d-flex justify-content-center">
    <div class="table-responsive" style="max-width: 800px;">
      <div class="d-flex justify-content-end">
        @if($isSuperAdmin)
        <button class="btn btn-success btn-sm" id="addCompanyBtn">Neue Firma hinzufügen</button>
        @endif
      </div>
      <table class="table table-bordered table-striped table-hover mt-3" id="companiesTable">
        <thead class="thead-dark">
          <tr>
            <th>Lexware-Benutzername</th>
            <th>Lexware-Passwort</th>
            @if($isSuperAdmin)
            <th style="width: 100px;">Aktionen</th> <!-- Adjusted the width here -->
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($companies as $company)
          @if($company->Lexware_Username)
          <tr data-id="{{ $company->id }}">
            <td class="p-0 m-0">
              <input type="text" class="form-control form-control-sm px-2" name="Lexware_Username"
                value="{{ $company->Lexware_Username }}" {{ $isSuperAdmin ? '' : 'readonly' }}>
            </td>
            <td class="p-0 m-0">
              <input type="text" class="form-control form-control-sm px-2" name="Lexware_Password"
                value="{{ $company->Lexware_Password }}" {{ $isSuperAdmin ? '' : 'readonly' }}>
            </td>
            @if($isSuperAdmin)
            <td class="p-0 m-0 text-center">
              <button class="btn btn-primary btn-sm p-1 m-0 update-btn"><i class="fa fa-refresh"></i></button>
              <!-- <button class="btn btn-danger btn-sm p-1 m-0 delete-btn"><i class="fa fa-trash"></i></button> -->
            </td>
            @endif
          </tr>
          @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>

@endsection

@section('script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<script>
  $(document).ready(function () {
    @if ($isSuperAdmin)
      // Add new company row
      $('#addCompanyBtn').click(function () {
        $('#companiesTable tbody').append(`
        <tr>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm px-2" name="Lexware_Username"></td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm px-2" name="Lexware_Password"></td>
            <td style="padding: 0; margin: 0; text-align: center;">
                <button class="btn btn-primary btn-sm p-1 m-0 save-btn"><i class="fa fa-save"></i></button>
                <button class="btn btn-danger btn-sm p-1 m-0 delete-btn"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
      `);
      });

    // Save new company
    $(document).on('click', '.save-btn', function () {
      let row = $(this).closest('tr');
      let saveButton = $(this);
      let data = {
        Lexware_Username: row.find('input[name="Lexware_Username"]').val(),
        Lexware_Password: row.find('input[name="Lexware_Password"]').val(),
        city_id: {{ $place-> id
    }},
    _token: '{{ csrf_token() }}'
      };

  saveButton.prop('disabled', true); // Disable save button to prevent multiple clicks

  $.post('/practice-companies', data, function (response) {
    if (response.success) {
      row.attr('data-id', response.company.id);
      row.find('.save-btn').replaceWith('<button class="btn btn-primary btn-sm p-1 m-0 update-btn"><i class="fa fa-refresh"></i></button>');
      toastr.success('Firma erfolgreich hinzugefügt.');
    }
  }).fail(function () {
    alert('Fehler beim Speichern der Firma.');
    saveButton.prop('disabled', false); // Re-enable save button in case of error
  });
    });

  // Update existing company
  $(document).on('click', '.update-btn', function () {
    let row = $(this).closest('tr');
    let id = row.data('id');
    let data = {
      Lexware_Username: row.find('input[name="Lexware_Username"]').val(),
      Lexware_Password: row.find('input[name="Lexware_Password"]').val(),
      city_id: {{ $place-> id
  }},
    _method: 'PUT',
    _token: '{{ csrf_token() }}'
      };

  $.ajax({
    url: `/practice-companies/${id}`,
    type: 'POST',
    data: data,
    success: function (response) {
      if (response.success) {
        toastr.success('Firma erfolgreich aktualisiert.');
      }
    },
    error: function () {
      alert('Fehler beim Aktualisieren der Firma.');
    }
  });
    });

  // Delete company with confirmation
  $(document).on('click', '.delete-btn', function () {
    let row = $(this).closest('tr');
    let id = row.data('id');

    if (confirm('Sind Sie sicher, dass Sie diese Firma löschen möchten?')) {
      if (id) {
        $.ajax({
          url: `/practice-companies/${id}`,
          type: 'POST',
          data: {
            _method: 'DELETE',
            _token: '{{ csrf_token() }}'
          },
          success: function (response) {
            if (response.success) {
              row.remove();
              toastr.success('Firma erfolgreich gelöscht.');
            }
          },
          error: function () {
            alert('Fehler beim Löschen der Firma.');
          }
        });
      } else {
        row.remove();
        toastr.success('Zeile erfolgreich entfernt.');
      }
    }
  });
  @endif
  });
</script>

@endsection