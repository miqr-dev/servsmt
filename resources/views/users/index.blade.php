@extends('layouts.admin_layout.admin_layout')

@section('content')
<section class="content">
	<div class="container-fluid">
    <div class="row">
      <div class="col-10 mx-auto">
        <div>
          <h2>Benutzerverwaltung</h2>
          <a href="{{ route('setting_index') }}" class="btn btn-outline-back float-left mb-3" data-toggle="tooltip" data-placement="right" title="Zurück "><i class="fas fa-undo-alt"></i></a>
        </div>
      </div>
    </div>
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
      <p>{{ $message }}</p>
    </div>
    @endif
    <div class="row">
      <div class="col-10 mx-auto">
        <table class="table table-sm" id="user_managment">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Name</th>
              <th scope="col">Email</th>
              <th scope="col">Status</th>
              <th scope="col">Roles</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
        @foreach ($data as $key => $user)
            <tr>
              <td>{{ ++$i }}</td>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>
                @if($user->hasAnyRole($collectionOfRoles))
                <div>
                  <div class="spinner-grow spinner-grow-sm text-success">
                  </div>
                  <span class="text-success ml-2">Active</span>
                </div>
                @else
                <div>
                  <div class="spinner-grow spinner-grow-sm text-danger">
                  </div>
                  <span class="text-danger ml-2">Inactive</span>
                </div>
                @endif
              </td>
              <td>
              @if(!empty($user->getRoleNames()))
                @foreach($user->getRoleNames() as $v)
                <label class="badge badge-success">{{ $v }}</label>
                @endforeach
              @endif
              </td>
              <td class="text-right">
              <a href="{{ route('users.show',$user->id) }}" class="btn btn-outline-info" data-toggle="tooltip" data-placement="top" title="Aussicht "><i class="far fa-eye"></i></a>
              <a href="{{ route('users.edit',$user->id) }}" class="btn btn-outline-primary" data-toggle="tooltip" data-placement="top" title="Bearbeiten"><i class="fas fa-pen-alt"></i></a>
              {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline']) !!}
              {{ Form::button('<i class="far fa-trash-alt"></i>', ['type'=>'submit', 'class' => 'btn btn-outline-danger']) }}
              {!! Form::close() !!}
              </td>
            </tr>
        @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection



@section('script')
<script>
  $(document).ready(function() {
    $('#user_managment').DataTable({
      "order": [[ 0, "desc" ]], // Sort by first column descending
      "pageLength": 50,         // Set 50 rows per page
      "searching": true,        // Enable live search
      "language": {
        "search": "_INPUT_",
        "searchPlaceholder": "Search..." // Placeholder text for the search box
      }
    });
  });
</script>

@endsection
