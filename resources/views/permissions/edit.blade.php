@extends('layouts.admin_layout.admin_layout')

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-6 mx-auto">
        <div class="pull-left">
          <h2>Permission bearbeiten</h2>
        </div>
        <div class="pull-right">
          <a class="btn btn-primary" href="{{ route('permissions.index') }}"> Back</a>
        </div>
      </div>
    </div>
    @if (count($errors) > 0)
    <div class="alert alert-danger">
      <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
    @endif
    {!! Form::model($permission, ['method' => 'PATCH', 'route' => ['permissions.update', $permission->id]]) !!}
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-6 mx-auto">
        <div class="form-group">
          <strong>Name:</strong>
          {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
        </div>
        <div class="form-group">
          <strong>Kategorie:</strong>
          {!! Form::select('permissioncategory_id', $categories, $permission->permissioncategory_id, array('placeholder' => '-- keine --', 'class' => 'form-control')) !!}
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 text-center">
        <button type="submit" class="btn btn-primary">Speichern</button>
      </div>
    </div>
  </div>

  {!! Form::close() !!}
</section>
@endsection
