@extends('layouts.admin_layout.admin_layout')
<!-- <link rel="stylesheet" href="{{ url ('bootstrap_modal/bootstrap-side-modals.css') }}"> -->
@section('content')
<!-- Main Content -->
<section class="content">
	<div class="container-fluid">
    <div class="row">
      <div class="col-6 mx-auto">
        <div class="float-left">
          <h2>Rollenverwaltung</h2>
        </div>
        <div class="float-right">
          <a href="{{ route('roles.create') }}" class="btn btn-outline-success"><i class="fas fa-plus"></i> Neue Rolle</a>
          <a href="{{ route('permissions.create') }}" class="btn btn-outline-success"><i class="fas fa-plus"></i> Neue Permission</a>
        </div>
      </div>
    </div>
		<div class="row">
      <div class="col-6 mx-auto">
      <table class="table table-sm">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Rolle</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($roles as $key => $role)
          <tr>
            <th scope="row">{{$role->id}}</th>
            <td>{{$role->name}}</td>
            <td class="text-right">
              <a href="{{ route('roles.show',$role->id) }}" class="btn btn-outline-info"><i class="far fa-eye"></i></a>
              @can('role-edit')
              <a href="{{ route('roles.edit',$role->id) }}" class="btn btn-outline-primary"><i class="fas fa-pen-alt"></i></a>
              @endcan
              @can('role-delete')
              {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id],'style'=>'display:inline']) !!}
              {{ Form::button('<i class="far fa-trash-alt"></i>', ['type'=>'submit', 'class' => 'btn btn-outline-danger']) }}
              {!! Form::close() !!}
              @endcan
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      {!! $roles->render() !!}
      </div>
		</div>
	</div>
</section>

@endsection

@section('script')

@endsection