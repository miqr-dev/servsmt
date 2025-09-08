@extends('layouts.admin_layout.admin_layout')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-6 mx-auto">
        <h2>{{ $role->name }}</h2>
      <a href="{{ route('roles.index') }}" class="btn btn-outline-back float-left mb-3" data-toggle="tooltip" data-placement="right" title="Zurück "><i class="fas fa-undo-alt"></i></a>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
  <!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-6 mx-auto">
        <div class="row">
          <div class="col-6">
            <div class="form-group">
              <strong>Berechtigungen:</strong>
              @if(!empty($rolePermissions))
              @foreach($rolePermissions as $v)
              <h5><span class="badge badge-success">{{ $v->name }},</span></h5>
              @endforeach
              @endif
            </div>
          </div>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
  <!-- /.content -->
<!-- /.content-wrapper -->

@endsection


  