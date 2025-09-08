@extends('layouts.admin_layout.admin_layout')

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="row justify-content-md-center">
      <div class="col-md-6">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h4>Projekt bearbeiten</h4>
            <form action="{{ route('projects.update',$project->id) }}" method="POST">
            @csrf
            @method('PUT')
          </div><!-- /.card-Header -->
            <div class="card-body">
              <div class="form-group">
                <label for="name">Projektname</label>
                <input type="text" name="name" class="form-control" value="{{$project->name}}" required>
              </div>
              <div class="form-group">
                <label for="description">Projektbeschreibung</label>
                <textarea name="description" class="form-control" rows="4"  required>{{$product->description}}</textarea>
              </div>
              <div class="form-group">
                <label for="inputStatus">Status</label>
                <select class="form-control custom-select" name="status" required>
                  <option selected="" disabled="">Bitte Wählen</option>
                  <option value="geplant">geplant</option>
                  <option value="geplant">In Bearbeitung</option>
                  <option value="erledigt">Erledigt</option>
                  <option value="Geschlossen">Geschlossen</option>
                  <option value="Abgebrochen">Abgebrochen</option>
                  <option value="in Warteposition">In Warteposition</option>
                </select>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="inputClientCompany">Startdatum</label>
                  <input type="text" class="form-control startdate" name="start_date" required>
                </div>
                <div class="form-group col-md-6">
                  <label for="inputProjectLeader">Enddatum</label>
                  <input type="text" name="end_date" class="form-control enddate" required>
                </div>
              </div>
              <div class="form-group">
                <label for="inputStatus"></label>
                <select class="form-control custom-select" name="assignedTo" required>
                  <option selected="" disabled="">Zuweisen</option>
                  @foreach($admins as $admin)
                  <option value="{{$admin->id}}">{{$admin->username}}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="btn btn-primary float-right">Submit</button>
            </div><!-- /.card-body -->
          </form>
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section>


@endsection
@section('script')
<script>

$(function() {
  moment.locale('de');
  $('.startdate').daterangepicker({
		singleDatePicker: true,
		showDropdowns: true,
		minYear: parseInt(moment().format('YYYY'))-1,
		maxYear: parseInt(moment().format('YYYY'))+1,
		locale: {
			format: 'DD-MM-YYYY'
		}
  });

  $('.enddate').daterangepicker({
		singleDatePicker: true,
		showDropdowns: true,
		minYear: parseInt(moment().format('YYYY'))-1,
		maxYear: parseInt(moment().format('YYYY'))+1,
		locale: {
			format: 'DD-MM-YYYY'
		}
  });
});
</script>

@endsection

