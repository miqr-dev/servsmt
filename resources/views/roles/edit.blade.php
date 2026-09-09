@extends('layouts.admin_layout.admin_layout')
@section('content')
<!-- Main Content -->
<div class="card mx-auto" style="width: 90%">
  {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
  <div class="card-header"><h2>Berechtigungen{!! Form::text('name', null, array('placeholder' => 'Rolle','class' => 'form-control mt-2')) !!} </h2></div>
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
          <input type="checkbox" class="custom-control-input" id="cat_{{ $loop->index }}">
          <label for="cat_{{ $loop->index }}" class="custom-control-label"><h5>{{ $categoryName }}</h5></label>
          <ul>
            @foreach($perms as $per)
            <li class="custom-control custom-checkbox">
              <input type="checkbox" name="permission[]" class="custom-control-input" id="perm_{{ $per->id }}" value="{{ $per->id }}"
                {{ $role->permissions->pluck('id')->contains($per->id) ? 'checked' : '' }}>
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
    <button type="submit" class="btn btn-success">Ändern</button>
    <a href="{{ route('roles.index') }}" class="btn btn-danger">Verwerfen</a>
  </div>
</div>
{!! Form::close() !!}
@endsection

@section('script')
<script>
$(document).ready(function(){
    $('input[type="checkbox"]').each(function(){
        if ($(this).prop("checked") == true) {
            console.log("Checkbox is checked.");
        } else if ($(this).prop("checked") == false) {
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
