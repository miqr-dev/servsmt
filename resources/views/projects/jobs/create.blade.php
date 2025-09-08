@extends('layouts.admin_layout.admin_layout')

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="row justify-content-md-center">
      <div class="col-md-6">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h4>Neues Projekt erstellen</h4>
            <form action="{{ route('job.store',['project' => $project])}}" method="POST">
            @csrf
          </div><!-- /.card-Header -->
            <div class="card-body">
              <div class="form-group">
                <label for="name">Beschäftigungsname</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="description">Beschreibung</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
              </div>
              <div class="form-group">
                <label for="inputStatus"></label>
                <select class="form-control custom-select" name="worker_id" required>
                  <option selected="" disabled="">Zuweisen</option>
                  @foreach($admins as $admin)
                  <option value="{{$admin->id}}">{{$admin->username}}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="btn btn-primary float-right">Einreichen</button>
            </div><!-- /.card-body -->
          </form>
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section>


@endsection
@section('script')
<script>

</script>

@endsection


