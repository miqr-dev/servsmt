@extends('layouts.admin_layout.admin_layout')
@section('content')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-6 mx-auto">
          <h1>{{$user->name}} <small>bearbeiten</small> </h1>
        <a href="{{ route('settings.addressbook') }}" class="btn btn-outline-back float-left mb-3" data-toggle="tooltip" data-placement="right" title="Zurück "><i class="fas fa-undo-alt"></i></a>
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
              {!! Form::model($user, ['method' => 'PATCH','route' => ['settings.addressbook_update', $user->id]]) !!}
              <h3 class="profile-username text-center">{{ $user->name }}</h3>
              <p class="text-muted text-center">{{$user->abteilung}}</p>
              <ul class="list-group list-group-unbordered mb-3">
                <li class="list-group-item">
                  <strong><i class="fas fa-user-md"></i> Position</strong><a class="float-right">{{$user->position}}</a>
                </li>
                <li class="list-group-item">
                  <strong><i class="fas fa-phone-alt"></i> Rufnummer</strong><a class="float-right">{{$user->tel}}</a>
                </li>
                <li class="list-group-item">
                  <strong><i class="far fa-envelope"></i> Mail</strong><a class="float-right">{{$user->email}}</a>
                </li>
                <li class="list-group-item">
                  <strong><i class="fas fa-map-marker-alt"></i> Standort</strong><a class="float-right">{{$user->straße}} , {{$user->bundesland}}</a>
                </li>
                <li class="list-group-item">
                  <strong><i class="fas fa-tag"></i> Nummern</strong>
                </li>
                  <li class="list-group-item">
                    {!! Form::select('telephones[]', $telephones,$userTelephone, array('class' => 'col-md-9 select22','multiple')) !!}
                  <button type="submit" class="btn btn-outline-success col-md-2 float-right">Einreichen</button>
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
    $('.select22').select2();
});
</script>
@endsection



