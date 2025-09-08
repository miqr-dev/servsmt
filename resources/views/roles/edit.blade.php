@extends('layouts.admin_layout.admin_layout')
@section('content')
<!-- Main Content -->
<div class="card mx-auto" style="width: 80%">
  {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
  <div class="card-header"><h2>Berechtigungen{!! Form::text('name', null, array('placeholder' => 'Rolle','class' => 'form-control mt-2')) !!} </h2></div>
  <div class="card-body">
    <div class="row">
      <div class="col-lg-6 border-right border-primary">
        <h5><i class="fas fa-arrow-alt-circle-right" style="color:#5bc0de;"></i> &nbsp;<em><u>Einstellungen</u></em></h5>
      </div><!--end first row-->
      <div class="col-lg-3">
        <h5><i class="fas fa-arrow-alt-circle-right" style="color:#6d28d9;"></i> &nbsp;<em><u>Ticket</u></em></h5>
      </div><!--end first row-->
      <div class="col-lg-3">
        <h5><i class="fas fa-arrow-alt-circle-right" style="color:#166534;"></i> &nbsp;<em><u>Teilnehmer Info</u></em></h5>
      </div><!--end first row-->
    </div>
    <div class="row mt-1">
      <div class="col-lg-6 border-right border-primary">
        <div class="row">
          <div class="col-lg-6">
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
          <div class="col-lg-6">
            <li class="custom-control custom-checkbox">
              <input type="checkbox" name="geräte" class="custom-control-input" id="Rollen">
              <label for="Rollen" class="custom-control-label" style="color:#F08080;"><h5>Rollen</h5></label>
              <ul>
                @foreach($permission as $per)
                  @if($per['category']['name'] == 'Rollen')
                    <li class="custom-control custom-checkbox">
                    <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
                    {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
                    <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
                    </li>
                  @endif
                @endforeach
              </ul>
            </li>
          </div> <!-- col-4 -->
      </div>
      </div>
      <div class="col-lg-6">
        <div class="row">
          <div class="col-lg-6">
            <li class="custom-control custom-checkbox">
              <input type="checkbox" name="ticket" class="custom-control-input" id="ticket">
              <label for="ticket" class="custom-control-label" style="color:#6d28d9;"><h5>Ticket</h5></label>
              <ul>
                @foreach($permission as $per)
                @if($per['category']['name'] == 'Ticket')
              <li class="custom-control custom-checkbox">
                <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
                {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
                <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
                </li>
                  @endif
                  @endforeach
              </ul>
            </li>
          </div> <!-- col-6 -->
          <div class="col-lg-6">
            <li class="custom-control custom-checkbox">
              <input type="checkbox" name="teilnehmer_info" class="custom-control-input" id="teilnehmer_info">
              <label for="teilnehmer_info" class="custom-control-label" style="color:#166534;"><h5>Teilnehmer Information</h5></label>
              <ul>
                @foreach($permission as $per)
                @if($per['category']['name'] == 'Teil_info')
              <li class="custom-control custom-checkbox">
                <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
                {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
                <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
                </li>
                  @endif
                  @endforeach
              </ul>
            </li>
          </div> <!-- col-3 -->
        </div>
      </div>
    </div> <!-- /.row -->
    <hr>
    <div class="row mt-3">
      <div class="col-md-12">
        <h5><i class="fas fa-arrow-alt-circle-right" style="color:#f0ad4e;"></i> &nbsp;<em><u>Inventur</u></em></h5>
      </div><!--end third row-->
    </div>
    <div class="row mt-1">
    <div class="col-3">
      <li class="custom-control custom-checkbox">
        <input type="checkbox" name="geräte" class="custom-control-input" id="geräte">
        <label for="geräte" class="custom-control-label" style="color:#5bc0de;"><h5>Geräte</h5></label>
        <ul>
          @foreach($permission as $per)
          @if($per['category']['name'] == 'Geräte')
        <li class="custom-control custom-checkbox">
          <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
          {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
          <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
          </li>
            @endif
            @endforeach
        </ul>
      </li>
    </div> <!-- col-3 -->
    <div class="col-3">
    <li class="custom-control custom-checkbox">
      <input type="checkbox" name="Erfassen" class="custom-control-input" id="Erfassen">
      <label for="Erfassen" class="custom-control-label" style="color:#5bc0de;"><h5>Erfassen</h5></label>
      <ul>
        @foreach($permission as $per)
          @if($per['category']['name'] == 'Erfassen')
            <li class="custom-control custom-checkbox">
            <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
            {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
            <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
            </li>
          @endif
        @endforeach
      </ul>
    </li>
    </div> <!-- col-3 -->
    <div class="col-3">
      <li class="custom-control custom-checkbox">
        <input type="checkbox" name="geräte" class="custom-control-input" id="Drucken">
        <label for="Drucken" class="custom-control-label" style="color:#007bff;"><h5>Drucken</h5></label>
        <ul>
          @foreach($permission as $per)
            @if($per['category']['name'] == 'Drucken')
              <li class="custom-control custom-checkbox">
              <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
              {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
              <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
              </li>
            @endif
          @endforeach
        </ul>
      </li>
    </div> <!-- col-3 -->
    <div class="col-3">
      <li class="custom-control custom-checkbox">
          @foreach($permission as $per)
            @if($per['category']['name'] == '')
              <li class="custom-control custom-checkbox">
              <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
              {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
              <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
              </li>
            @endif
          @endforeach
      </li>
    </div> <!-- col-3 -->
  </div> <!-- /.row -->
  <hr>
  <div class="row mt-3">
    <div class="col-md-12">
      <h5><i class="fas fa-arrow-alt-circle-right" style="color:#58355E;"></i> &nbsp;<em><u>Projekt</u></em></h5>
    </div><!--end third row-->
  </div>
  <div class="row mt-1">
    <div class="col-3">
      <li class="custom-control custom-checkbox">
        <input type="checkbox" name="ticket" class="custom-control-input" id="ticket">
        <label for="ticket" class="custom-control-label" style="color:#5bc0de;"><h5>Ticket</h5></label>
        <ul>
          @foreach($permission as $per)
          @if($per['category']['name'] == 'Ticket')
        <li class="custom-control custom-checkbox">
          <input type="checkbox" name="permission[]" class="custom-control-input" id="{{ $per->id }}"  value="{{ $per->id }}"
          {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
          <label for="{{ $per->id }}" class="custom-control-label">{{$per->name}}</label>
          </li>
            @endif
            @endforeach
        </ul>
      </li>
    </div> <!-- col-3 -->
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
    <button type="submit" class="btn btn-success">Ändern</button>
    <a href="{{ route('roles.index') }}" class="btn btn-danger">Verwerfen</a>
  </div>
</div>


{!! Form::close() !!}
@endsection

@section('script')
<script>

$(document).ready(function(){
        $('input[type="checkbox"]').ready(function(){
            if($(this).prop("checked") == true){
                console.log("Checkbox is checked.");
            }
            else if($(this).prop("checked") == false){
                console.log("Checkbox is unchecked.");
            }
        });
    });

$(function() {

$('input[type="checkbox"]').change(checkboxChanged);

function checkboxChanged() {
  var $this = $(this), // The clicked upon checkbox
      checked = $this.prop("checked"), // The new state of the checbox (true or false)
      container = $this.parent(); // The li container of the checkbox

  container.find('input[type="checkbox"]') // 1. Get all the child checkboxes of the container
  .prop({                                  // 2. Change the properties of all such checkboxes
      indeterminate: false,
      checked: checked
  })
  .siblings('label')                       // 3. Get their corresponding labels
  .removeClass('custom-checked custom-unchecked custom-indeterminate') // 4. Change their CSS classes
  .addClass(checked ? 'custom-checked' : 'custom-unchecked');

  checkSiblings(container, checked);       // Check the siblings of the container
}

function checkSiblings($el, checked) { // $el is a li
  var parent = $el.parent().parent(),  // parent is the containing li element
      all = true,
      indeterminate = false;

  $el.siblings().each(function() { // for each li sibling of the current element
    all = all && ($(this).children('input[type="checkbox"]').prop("checked") === checked); 
  });
  
  if (all && checked) {
    parent.children('input[type="checkbox"]')
    .prop({
        indeterminate: false,
        checked: checked
    })
    .siblings('label')
    .removeClass('custom-checked custom-unchecked custom-indeterminate')
    .addClass(checked ? 'custom-checked' : 'custom-unchecked');

    checkSiblings(parent, checked);
  } 
  else if (all && !checked) {
    
    numChecked = parent.children('ul').find('input[type="checkbox"]:checked').length;
    
    indeterminate = numChecked > 0;

    parent.children('input[type="checkbox"]')
    .prop("checked", checked)
    .prop("indeterminate", indeterminate)
    .siblings('label')
    .removeClass('custom-checked custom-unchecked custom-indeterminate')
    .addClass(indeterminate ? 'custom-indeterminate' : (checked ? 'custom-checked' : 'custom-unchecked'));

    checkSiblings(parent, checked);
  } 
  else {
    $el.parents("li").children('input[type="checkbox"]')
    .prop({
        indeterminate: true,
        checked: false
    })
    .siblings('label')
    .removeClass('custom-checked custom-unchecked custom-indeterminate')
    .addClass('custom-indeterminate');
  }
}
});





</script>
@endsection