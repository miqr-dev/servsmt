@extends('layouts.admin_layout.admin_layout')
<style>
  .custom-btn {
    min-width: 200px;
    height: 38px;
  }
</style>
@section('content')

<div class="container">
  <h1 class="my-3 text-center" style="font-size:2.5rem; font-weight: bold;">Terminal-Datenverwaltung in {{
    $place->pnname }}</h1>

  @if($isSuperAdmin)

  @endif


  <div class="container d-flex justify-content-center">
    <div class="table-responsive" style="max-width: 1200px;">
      <div class="d-flex justify-content-between">
        @if($isSuperAdmin)
        <a href="{{ route('practice-companies.export', ['city' => $place->pnname]) }}" class="btn btn-info mb-3">In
          Excel exportieren</a>
        <button class="btn btn-success btn-sm custom-btn" id="addCompanyBtn"> Hinzufügen</button>
        @endif
      </div>

      <table class="table table-bordered table-striped table-hover mt-3" id="companiesTable">
        <thead class="thead-dark">
          <tr>
            <th>Windows-Benutzername</th>
            <th>Windows-Passwort</th>
            <th>E-Mail-Benutzername</th>
            <th>E-Mail-Passwort</th>
            <th>Aktuell</th>
            @if($isSuperAdmin)
            <th>Aktionen</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($companies as $company)
          @if($company->Windows_Username)
          <tr data-id="{{ $company->id }}">
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm"
                name="Windows_Username" value="{{ $company->Windows_Username }}" {{ $isSuperAdmin ? '' : 'readonly' }}>
            </td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm"
                name="Windows_Password" value="{{ $company->Windows_Password }}" {{ $isSuperAdmin ? '' : 'readonly' }}>
            </td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm"
                name="Email_Username" value="{{ $company->Email_Username }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm"
                name="Email_Password" value="{{ $company->Email_Password }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm" name="aktuell"
                value="{{ $company->aktuell }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
            @if($isSuperAdmin)
            <td style="padding: 0; margin: 0;">
              <button class="btn btn-primary btn-sm update-btn"><i class="fa fa-refresh"></i></button>
              <button class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
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
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm" name="Windows_Username"></td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm" name="Windows_Password"></td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm" name="Email_Username"></td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm" name="Email_Password"></td>
            <td style="padding: 0; margin: 0;"><input type="text" class="form-control form-control-sm" name="aktuell"></td>
            <td style="padding: 0; margin: 0;">
                <button class="btn btn-primary btn-sm save-btn"><i class="fa fa-save"></i></button>
                <button class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
      `);
      });

    // Save new company
    $(document).on('click', '.save-btn', function () {
      let row = $(this).closest('tr');
      let saveButton = $(this);
      let data = {
        Windows_Username: row.find('input[name="Windows_Username"]').val(),
        Windows_Password: row.find('input[name="Windows_Password"]').val(),
        Email_Username: row.find('input[name="Email_Username"]').val(),
        Email_Password: row.find('input[name="Email_Password"]').val(),
        Aktuell: row.find('input[name="aktuell"]').val(),
        place_id: {{ $place->id}},
    _token: '{{ csrf_token() }}'
      };

  saveButton.prop('disabled', true); // Disable save button to prevent multiple clicks

  $.post('/practice-companies', data, function (response) {
    if (response.success) {
      row.attr('data-id', response.company.id);
      row.find('.save-btn').replaceWith('<button class="btn btn-primary btn-sm update-btn"><i class="fa fa-refresh"></i></button>');
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
      Windows_Username: row.find('input[name="Windows_Username"]').val(),
      Windows_Password: row.find('input[name="Windows_Password"]').val(),
      Email_Username: row.find('input[name="Email_Username"]').val(),
      Email_Password: row.find('input[name="Email_Password"]').val(),
      Aktuell: row.find('input[name="aktuell"]').val(),
      place_id: {{ $place-> id }},
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