
@extends('layouts.admin_layout.admin_layout')
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">
          <div class="col-6 mx-auto">
            <h1>herzlich willkommen  !!!</h1>
            <h1>{{$user->name}} <small></small> </h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-6 mx-auto">
          <!-- Profile Image -->
          <div class="card card-primary card-outline">
            <div class="card-body box-profile">
              {!! Form::model($user, ['method' => 'PATCH','route' => ['settings.firstupdate', $user->id]]) !!}
              <h3 class="profile-username text-center">{{ $user->name }}</h3>
              <p class="text-muted text-center">{{$user->abteilung}}</p>
              <ul class="list-group list-group-unbordered mb-3">
                <input type="hidden" name="lastlogin" value="<?php echo date('Y-m-d H:i:s'); ?>">
                <li class="list-group-item">
                  <strong><i class="fas fa-user-md"></i> Position</strong>
                  <input class="float-right" type="text" name="position" value=" {{old('position') ?? $user->position}}">
                </li>
                <li class="list-group-item">
                  <strong><i class="fas fa-phone-alt"></i> Rufnummer</strong>
                  <input type="text" name="tel" class="float-right" value="{{old('tel') ?? $user->tel}}">
                </li>
                <li class="list-group-item">
                  <strong><i class="far fa-envelope"></i> Mail</strong>
                  <input type="text" name="email" class="float-right" value="{{old('email') ?? $user->email}}">
                </li>
                <li class="list-group-item">
                  <strong><i class="fas fa-map-marker-alt"></i> Standort</strong>
                  <input type="text" name="straße" class="float-right" value="{{old('straße') ?? $user->straße}}">
                </li>
                  <li class="list-group-item">
                  <button type="submit" class="btn btn-outline-success col-md-2 ">Submit</button>
                </li>
              </ul>
              <!-- /.card-body -->
            </div>
            {!! Form::close() !!}
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
    <!-- /.content -->
  <!-- /.content-wrapper -->



@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
@endsection
