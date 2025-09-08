@extends('layouts.admin_layout.admin_layout')
@section('content')
<!-- Main Content -->
<div class="card mx-auto" style="width: 80%">
  {!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
  <div class="card-header"><h2>Neue Rolle erstellen{!! Form::text('name', null, array('placeholder' => 'Neue Rolle','class' => 'form-control mt-2')) !!}</h2></div>
  <div class="card-body">
  <div class="row">
  <ul>
  <div class="col-md-12">
    <h5><i class="fas fa-arrow-alt-circle-right" style="color:#5bc0de;"></i> &nbsp;<em><u>Einstellungen</u></em></h5>
  </div><!--end first row-->
  </div>
  <div class="row mt-1">
    <div class="col-4">
      <h5>Inventur Einstellungen</h5>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" name="enter_city" class="custom-control-input settings-inventory-checkbox" id="enter_city" value="">
        <label class="custom-control-label" for="enter_city">Stadt hinzufügen</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" name="enter_address" class="custom-control-input settings-inventory-checkbox" id="enter_address">
        <label class="custom-control-label" for="enter_address">Adresse hinzufügen</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" name="enter_room" class="custom-control-input settings-inventory-checkbox" id="enter_room">
        <label class="custom-control-label" for="enter_room">Raum hinzufügen</label>
      </div>
    </div> <!-- col-4 -->
    <div class="col-4">
      <h5>Rollen</h5>
      @foreach($permission as $per)
        @if($per['category']['name'] == 'Rollen')
        <div class="custom-control custom-checkbox">
        <input class="form-check-input" type="checkbox" name="permission[]" value="{{ $per->id }}">
          {{$per->name}}
        </div>
      @endif
      @endforeach
    </div> <!-- col-4 -->
    <div class="col-4">
      <h5>Benutzereinstellungen</h5>
    </div> <!-- col-4 -->
  </div> <!-- /.row -->
  <hr>
  <div class="row mt-3">
    <div class="col-md-12">
      <h5><i class="fas fa-arrow-alt-circle-right" style="color:#f0ad4e;"></i> &nbsp;<em><u>Inventur</u></em></h5>
    </div><!--end third row-->
  </div>
  <div class="row mt-1">
      <div class="col-3">
        <h5 class="lead" style="color: #F08080;">Geräte</h5>
        @foreach($permission as $per)
          @if($per['category']['name'] == 'Geräte')
          <div class="custom-control custom-checkbox">
          {{ Form::checkbox('permission[]', $per->id, false, array('class' => 'name')) }}
          {{ $per->name }}
          </div>
        @endif
        @endforeach
      </div> <!-- col-2 -->
      <div class="col-3">
        <h5 class="lead" style="color:  #5bc0de;">Erfassen</h5>
          @foreach($permission as $per)
            @if($per['category']['name'] == 'Erfassen')
        <div class="custom-control custom-checkbox">
          {{ Form::checkbox('permission[]', $per->id, false, array('class' => 'name')) }}
          {{ $per->name }}
        </div>
            @endif
          @endforeach
      </div> <!-- col-2 -->
      <div class="col-3">
        <h5 class="lead" style="color: #007bff;">Drucken</h5>
        @foreach($permission as $per)
         @if($per['category']['name'] == 'Drucken')
          <div class="custom-control custom-checkbox">
          {{ Form::checkbox('permission[]', $per->id, false, array('class' => 'name')) }}
          {{ $per->name }}
         </div>
          @endif
        @endforeach
      </div> <!-- col-2 -->
      <div class="col-3">
        @foreach($permission as $per)
          @if($per['category']['name'] == '')
          <div class="custom-control custom-checkbox">
          {{ Form::checkbox('permission[]', $per->id, false, array('class' => 'name')) }}
          {{ $per->name }}
          </div>
          @endif
       @endforeach
      </div> <!-- col-2 -->
  </div> <!-- /.row -->
  <hr>
  <div class="row mt-3">
    <div class="col-md-12">
      <h5><i class="fas fa-arrow-alt-circle-right" style="color: green"></i> &nbsp;<em><u>Standort</u></em></h5>
    </div><!--end third row-->
  </div>
  <div class="row mt-1">
    <div class="col-3">
      <h5 class="lead" style="color: #F08080;">Erfurt</h5>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="bhof_1" class="custom-control-input">
        <label class="custom-control-label" for="bhof_1">BHof 1</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="bhof_2" class="custom-control-input">
        <label class="custom-control-label" for="bhof_2">Bhof 2</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="bhof_4" class="custom-control-input">
        <label class="custom-control-label" for="bhof_4">Bhof 4</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="bhof_18" class="custom-control-input">
        <label class="custom-control-label" for="bhof_18">Bhof 18</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="h89" class="custom-control-input">
        <label class="custom-control-label" for="h89">H89</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="h92" class="custom-control-input">
        <label class="custom-control-label" for="h92">H92</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="otto_35" class="custom-control-input">
        <label class="custom-control-label" for="otto_35">Otto 35</label>
      </div>
    </div> <!-- End Erfurt -->
    <div class="col-3">
      <h5 class="lead" style="color:  #5bc0de;">Suhl</h5>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="blucher_6" class="custom-control-input">
        <label class="custom-control-label" for="blucher_6">Blücher 6</label>
      </div>
      <div class="custom-control custom-checkbox mb-3">
        <input type="checkbox" id="puschkin_1" class="custom-control-input">
        <label class="custom-control-label" for="puschkin_1">Puschkin 1</label>
      </div>
      <h5 class="lead" style="color: #007bff;">Leipzig</h5>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="landsberger_4" class="custom-control-input">
        <label class="custom-control-label" for="landsberger_4">Landsberger 4</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="landsberger_23" class="custom-control-input">
        <label class="custom-control-label" for="landsberger_23">Landsberger 23</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="mehring_3" class="custom-control-input">
        <label class="custom-control-label" for="mehring_3">Mehring 3</label>
      </div>
    </div> <!-- col-2 -->
    <div class="col-3">
      <h5 class="lead" style="color: #007bff;">Chemnitz</h5>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="barbarossa_2" class="custom-control-input">
        <label class="custom-control-label" for="barbarossa_2">Barbarossa 2</label>
      </div>
      <div class="custom-control custom-checkbox mb-3">
        <input type="checkbox" id="park_28" class="custom-control-input">
        <label class="custom-control-label" for="park_28">Park 28</label>
      </div>
      <h5 class="lead" style="color: #007bff;">Berlin</h5>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="trachenberg_93" class="custom-control-input">
        <label class="custom-control-label" for="trachenberg_93">Trachenberg 93</label>
      </div>
    </div> <!-- col-2 -->
    <div class="col-3">
      <h5 class="lead" style="color: #007bff;">Dresden</h5>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="glashutter_101" class="custom-control-input">
        <label class="custom-control-label" for="glashutter_101">glashütter 101</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="glashutter_101A" class="custom-control-input">
        <label class="custom-control-label" for="glashutter_101A">glashütter 101A</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" id="loscher_16" class="custom-control-input">
        <label class="custom-control-label" for="loscher_16">löscher_16</label>
      </div>
      <div class="custom-control custom-checkbox mb-3">
        <input type="checkbox" id="menelssohn_18" class="custom-control-input">
        <label class="custom-control-label" for="menelssohn_18">Menelssohn 18</label>
      </div>
    </div> <!-- col-2 -->
  </div> <!-- /.row -->
  </div> <!-- /.card Body -->
  <div class="card-footer text-right">
    <button type="submit" class="btn btn-success">Einfügen</button>
    <a href="{{ route('roles.index') }}" class="btn btn-danger">Verwerfen</a>
  </div>
</div>
{!! Form::close() !!}
@endsection
