@extends('layouts.admin_layout.admin_layout')

@section('content')

<div class="container">
  <h1 class="mt-5">Practice Companies</h1>
  @if($isSuperAdmin)
  <a href="{{ route('practice-companies.export') }}" class="btn btn-info mb-3">Export to Excel</a>
  @endif
  <table class="table table-bordered mt-3" id="companiesTable">
    <thead>
      <tr>
        <th>Windows Username</th>
        <th>Windows Password</th>
        <th>Lexware Username</th>
        <th>Lexware Password</th>
        <th>Email Username</th>
        <th>Email Password</th>
        @if($isSuperAdmin)
        <th>Actions</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @foreach($companies as $company)
      <tr data-id="{{ $company->id }}">
        <td><input type="text" class="form-control" name="Windows_Username" value="{{ $company->Windows_Username }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
        <td><input type="text" class="form-control" name="Windows_Password" value="{{ $company->Windows_Password }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
        <td><input type="text" class="form-control" name="Lexware_Username" value="{{ $company->Lexware_Username }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
        <td><input type="text" class="form-control" name="Lexware_Password" value="{{ $company->Lexware_Password }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
        <td><input type="text" class="form-control" name="Email_Username" value="{{ $company->Email_Username }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
        <td><input type="text" class="form-control" name="Email_Password" value="{{ $company->Email_Password }}" {{ $isSuperAdmin ? '' : 'readonly' }}></td>
        @if($isSuperAdmin)
        <td>
          <button class="btn btn-primary update-btn">Update</button>
          <button class="btn btn-danger delete-btn">Delete</button>
        </td>
        @endif
      </tr>
      @endforeach
    </tbody>
  </table>
  @if($isSuperAdmin)
  <button class="btn btn-success mt-3" id="addCompanyBtn">Add New Company</button>
  @endif
</div>

@endsection

@section('script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<script>
  $(document).ready(function () {
    @if($isSuperAdmin)
    // Add new company row
    $('#addCompanyBtn').click(function () {
      $('#companiesTable tbody').append(`
        <tr>
            <td><input type="text" class="form-control" name="Windows_Username"></td>
            <td><input type="text" class="form-control" name="Windows_Password"></td>
            <td><input type="text" class="form-control" name="Lexware_Username"></td>
            <td><input type="text" class="form-control" name="Lexware_Password"></td>
            <td><input type="text" class="form-control" name="Email_Username"></td>
            <td><input type="text" class="form-control" name="Email_Password"></td>
            <td>
                <button class="btn btn-primary save-btn">Save</button>
                <button class="btn btn-danger delete-btn">Delete</button>
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
        Lexware_Username: row.find('input[name="Lexware_Username"]').val(),
        Lexware_Password: row.find('input[name="Lexware_Password"]').val(),
        Email_Username: row.find('input[name="Email_Username"]').val(),
        Email_Password: row.find('input[name="Email_Password"]').val(),
        _token: '{{ csrf_token() }}'
      };

      saveButton.prop('disabled', true); // Disable save button to prevent multiple clicks

      $.post('/practice-companies', data, function (response) {
        if (response.success) {
          row.attr('data-id', response.company.id);
          row.find('.save-btn').replaceWith('<button class="btn btn-primary update-btn">Update</button>');
          toastr.success('Company added successfully.');
        }
      }).fail(function () {
        alert('Error saving company.');
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
        Lexware_Username: row.find('input[name="Lexware_Username"]').val(),
        Lexware_Password: row.find('input[name="Lexware_Password"]').val(),
        Email_Username: row.find('input[name="Email_Username"]').val(),
        Email_Password: row.find('input[name="Email_Password"]').val(),
        _method: 'PUT',
        _token: '{{ csrf_token() }}'
      };

      $.ajax({
        url: `/practice-companies/${id}`,
        type: 'POST',
        data: data,
        success: function (response) {
          if (response.success) {
            toastr.success('Company updated successfully.');
          }
        },
        error: function () {
          alert('Error updating company.');
        }
      });
    });

    // Delete company with confirmation
    $(document).on('click', '.delete-btn', function () {
      let row = $(this).closest('tr');
      let id = row.data('id');

      if (confirm('Are you sure you want to delete this company?')) {
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
                toastr.success('Company deleted successfully.');
              }
            },
            error: function () {
              alert('Error deleting company.');
            }
          });
        } else {
          row.remove();
          toastr.success('Row removed successfully.');
        }
      }
    });
    @endif
  });
</script>

@endsection
