@extends('layouts.admin_layout.admin_layout')
@section('content')
<!-- Main Content -->
<div class="card mx-auto" style="width: 90%">
  {!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
  <div class="card-header"><h2>Neue Rolle erstellen{!! Form::text('name', null, array('placeholder' => 'Neue Rolle','class' => 'form-control mt-2')) !!}</h2></div>
  <div class="card-body">
    @if (count($errors) > 0)
    <div class="alert alert-danger">
      <strong>Whoops!</strong> Es gab ein Problem mit deiner Eingabe.<br><br>
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    <div class="row mt-1">
      @foreach($permissionsByCategory as $categoryName => $perms)
      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <li class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input select-all-in-group" id="cat_{{ $loop->index }}">
          <label for="cat_{{ $loop->index }}" class="custom-control-label"><h5>{{ $categoryName }}</h5></label>
          <ul>
            @foreach($perms as $per)
            <li class="custom-control custom-checkbox">
              <input type="checkbox" name="permission[]" class="custom-control-input" id="perm_{{ $per->id }}" value="{{ $per->id }}">
              <label for="perm_{{ $per->id }}" class="custom-control-label">{{ $per->name }}</label>
            </li>
            @endforeach
          </ul>
        </li>
      </div>
      @endforeach
    </div>
  </div> <!-- /.card Body -->
  <div class="card-footer text-right">
    <button type="submit" class="btn btn-success">Einfügen</button>
    <a href="{{ route('roles.index') }}" class="btn btn-danger">Verwerfen</a>
  </div>
</div>
{!! Form::close() !!}
@endsection

@section('script')
<script>
$(function() {
  $('.select-all-in-group').change(function() {
    var checked = $(this).prop('checked');
    $(this).closest('li').find('ul input[type="checkbox"]').prop('checked', checked);
  });
});
</script>
@endsection
