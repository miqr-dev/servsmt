@extends('layouts.admin_layout.admin_layout')
<link rel="stylesheet" href="{{ url ('bootstrap_modal/bootstrap-side-modals.css') }}">
@section('content')

<!-- Main Content -->
<section class="content">
	<div class="container-fluid">
		<div class="row">
      <div class="col-6">
      <table class="table table-sm">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Rolle</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($roles as $role)
          <tr>
            <th scope="row">{{$role->id}}</th>
            <td>{{$role->name}}</td>
            <td class="text-right">
              <button id="role_view_modal" class="btn btn-outline-success" data-id="{{ $role->id }}"><i class="far fa-eye"></i></button>
              <button class="btn btn-outline-primary"><i class="fas fa-pen-alt"></i></button>
              <button class="btn btn-outline-danger"><i class="far fa-trash-alt"></i></button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      </div> <!-- col 3-->
		</div>
	</div>
</section>


@include('settings.modal_settings.roles.roles_view')
@endsection

@section('script')
<script>
  $(document).ready(function(){
	//**** Add City ****//
	$(document).on('click','#role_view_modal',function(){
    let role_id = $(this).data('id');
    console.log(role_id);
		$('#role_view').modal('show');
    $.ajax({
						type:'get',
						url:"{{ route('role_permissions') }}",
						success:function(resp){
							console.log(resp);
						},error:function(){
							alert("Error");
						}
					});
	});


}); //document ready




</script>
@endsection