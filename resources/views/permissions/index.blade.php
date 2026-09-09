@extends('layouts.admin_layout.admin_layout')

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-8 mx-auto">
        <div class="float-left">
          <h2>Berechtigungen</h2>
        </div>
        <div class="float-right">
          @can('permission-create')
          <a href="{{ route('permissions.create') }}" class="btn btn-outline-success"><i class="fas fa-plus"></i> Neue Permission</a>
          @endcan
          <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary"><i class="fas fa-user-tag"></i> Rollen</a>
        </div>
      </div>
    </div>
    @if(session('message'))
    <div class="row">
      <div class="col-8 mx-auto">
        <div class="alert alert-{{ session('alert-type', 'success') }}">{{ session('message') }}</div>
      </div>
    </div>
    @endif
    <div class="row">
      <div class="col-8 mx-auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Kategorie</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($permissions as $permission)
            <tr>
              <th scope="row">{{ $permission->id }}</th>
              <td>{{ $permission->name }}</td>
              <td>{{ optional($permission->category)->name ?? '—' }}</td>
              <td class="text-right">
                <a href="{{ route('permissions.show', $permission->id) }}" class="btn btn-outline-info"><i class="far fa-eye"></i></a>
                @can('permission-edit')
                <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-outline-primary"><i class="fas fa-pen-alt"></i></a>
                @endcan
                @can('permission-delete')
                {!! Form::open(['method' => 'DELETE', 'route' => ['permissions.destroy', $permission->id], 'style' => 'display:inline']) !!}
                {{ Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-danger', 'onclick' => "return confirm('Permission wirklich löschen?')"]) }}
                {!! Form::close() !!}
                @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        {{ $permissions->links() }}
      </div>
    </div>
  </div>
</section>
@endsection
