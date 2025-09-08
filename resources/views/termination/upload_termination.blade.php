@extends('layouts.admin_layout.admin_layout')

@section('content')

<section class="content">
	<div class="container-fluid">
    <div class="row">
      <div class="col-lg-12 mx-auto">
        <div>
          <h2>Benutzerverwaltung</h2>
          <a href="{{ route('setting_index') }}" class="btn btn-outline-back float-left mb-3" data-toggle="tooltip" data-placement="right" title="Zurück "><i class="fas fa-undo-alt"></i></a>
        </div>
      </div>
    </div>
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
      <p>{{ $message }}</p>
    </div>
    @endif
    <div class="row">
    <form action="{{route ('termination_upload')}}" method="post" enctype="multipart/form-data">
     @csrf
      <div class="col-lg-12 mx-auto">
        <div class="custom-file col-md-12">
          <input type="file" name="file">
        </div>
        <div>
          <button type="submit" class="btn btn-outline-success col-lg-12 float-right">Einreichen</button>
        </div>
      </div>
    </form>
    </div>
  </div>
</section>

@endsection

@section('script')
@endsection