@extends('layouts.admin_layout.admin_layout')


@section('content')
<section class="content">
	<div class="container-fluid">
    <div class="row">
      <div class="col-10 mx-auto">
        <div>
          <h2>Telefon Assignment</h2>
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
        <table class="table table-sm text-small align-middle" id="user_telephone_management">
          <thead>
            <tr>
              <th scope="col">Name</th>
              <th scope="col">Email</th>
              <th scope="col">Telefon</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
        @foreach ($users as $user)
            <tr>
              <td>{{ $user->name }}, &nbsp;{{ $user->vorname }}</td>
              <td>{{ $user->email }}</td>
              <td> 
              @if(count($user->telephones) > 0)
                @foreach($user->telephones as $telephone)
                {{ $telephone->gname }}
                @endforeach
              @endif
              </td>
              <td class="text-right">
              <a href="{{ route('settings.addressbook_edit',$user->id) }}" class="btn btn-outline-primary" data-toggle="tooltip" data-placement="top" title="Bearbeiten"><i class="fas fa-pen-alt"></i></a>
              <!-- {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline']) !!}
              {{ Form::button('<i class="far fa-trash-alt"></i>', ['type'=>'submit', 'class' => 'btn btn-outline-danger']) }}
              {!! Form::close() !!} -->
              </td>
            </tr>
        @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

@endsection @section('script')

<script>
  $(document).ready( function () {
    $('#user_telephone_management').DataTable();
  } );

</script>

@endsection
