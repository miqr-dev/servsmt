@extends('layouts.admin_layout.admin_layout')

@section('content')
<style>
	.list-group-item
{
  overflow:hidden;  
  position: relative;
  display: block;
  padding: 10px 15px;
  margin-bottom: -1px;
  background-color: #fff;
  border: 1px solid #ddd;
}
</style>


<!-- Main Content -->
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<!-- First Section -->
			<div class="col-3">
				<!-- Card  -->
				<div class="card card-primary card-outline">
					<div class="card-body">
						<h5 class="card-title mb-3"><strong>Inventur Einstellungen</strong></h5>
						<div class="card-text">
							<div class="list-group">
								<a href="javascript:" id="addCity_modal" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-map-marker-alt"></i> Neue Stadt hinzufügen</a>
								<a href="javascript:" id="addLocation_modal" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-building"></i> Neue Adresse hinzufügen
									</a>
								<a href="javascript:" id="addRoom_modal" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-chalkboard-teacher"></i> Neuen Raum hinzufügen
								</a>
							</div><!-- End Linst Group -->
						</div><!-- End Card Text -->
					</div><!-- End Card Body -->
				</div> <!-- End Card  -->
			</div><!-- End First Section -->
			<!-- second section -->
			<div class="col-3">
				<!-- Card  -->
				<div class="card card-primary card-outline">
					<div class="card-body">
						<h5 class="card-title mb-3"><strong>Benutzereinstellungen</strong></h5>
						<div class="card-text">
							<div class="list-group">
								<a href="{{ route('settings.usersList')}}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-users"></i> Benutzerverwaltung</a>
								<a href="{{ url ('/roles')}}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-user-tag"></i> Rollen & Berechtigungen</a>
								<a href="{{ route ('terminationCreate_upload')}}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-user-tag"></i> Mitarbeiteraustritt</a>
                </a>
							</div><!-- End Linst Group -->
						</div><!-- End Card Text -->
					</div><!-- End Card Body -->
				</div><!-- End Card  -->
			</div><!-- End Second Section -->
			<!-- third section -->
			<div class="col-3">
				<div class="card card-primary card-outline">
					<div class="card-body">
						<h5 class="card-title mb-3"><strong>Publish News</strong></h5>
						<div class="card-text">
							<div class="list-group">
								<a href="{{route('popup.show')}}" class="list-group-item list-group-item-action list-group-item-primary py-1">Dashboard Popup</a>
								<a href="{{route('newsbar.show')}}" class="list-group-item list-group-item-action list-group-item-primary py-1">Nachrichtenleiste</a>
								<a href="{{route('standortbesuch')}}" class="list-group-item list-group-item-action list-group-item-primary py-1">Standortbesuch</a>
							</div><!-- End Linst Group -->
						</div><!-- End Card Text -->
					</div><!-- End Card Body -->
				</div><!-- End Card  -->
			</div><!-- End Third Section -->
			<!-- Forth section -->
			<div class="col-3">
				<div class="card card-primary card-outline">
					<div class="card-body">
						<h5 class="card-title mb-3"><strong>Address Book</strong></h5>
						<div class="card-text">
							<div class="list-group">
								<a href="{{route('settings.addressbook')}}" class="list-group-item list-group-item-action list-group-item-primary py-1">Address Book</a>
								<a href="#" class="list-group-item list-group-item-action list-group-item-primary py-1">Hard Code</a>
							</div><!-- End Linst Group -->
						</div><!-- End Card Text -->
					</div><!-- End Card Body -->
				</div><!-- End Card  -->
			</div><!-- End Forth Section -->
		</div>
	</div>
</section>

@include('settings.modal_settings.inventory.add_city')
@include('settings.modal_settings.inventory.add_location')
@include('settings.modal_settings.inventory.add_room')


@endsection

@section('script')
<script>
$(document).ready(function(){
	//**** Add City ****//
	$(document).on('click','#addCity_modal',function(){
		$('#addCity').modal('show');
	});
	//**** Add Location ****//
    // 1 **** Add Location ****// 
      $(document).on('click','#addLocation_modal',function(){
        $('#addLocation').modal('show');
        $('#settings_cityList').find('option').remove();
        $('#settings_cityList').append(new Option("Standort...",''));
        $('#settings_address_input').prop('readonly',true);
        $.ajax({
          type: 'get',
          url: '{{route("settings.cityList")}}',
          }).done(function (data){
            $.each(data['cities'],function(index,item){
              $('#settings_cityList').append(new Option(item.pnname,item.id));
            });
          });
      });
      // 2 **** Add Address remove Grayed out input ****// 
      $(document).on('change','#settings_cityList',function(){
        $('#settings_address_input').prop('readonly',false);
        $('#settings_address_input').val('');
        $('#settings_cityList').find('option').get(0).remove();
      });
    
  //**** Add Room ****//
    // 1 **** Add Room **** //
      $(document).on('click','#addRoom_modal',function(){
        $('#addRoom').modal('show');
        $('#settings_AddressCityList').find('option').remove();
        $('#settings_AddressCityList').find('optgroup').remove();
        $('#settings_AddressCityList').append(new Option("Standort...",''));
        $('#settings_rname').prop('readonly',true);
        $('#settings_etage').prop('readonly',true);
        $('#settings_altrname').prop('readonly',true);
        $.ajax({
          type:'get',
          url:'{{route("settings.cityAddressList")}}',
        }).done(function(data){
          $.each(data['places'], function(index,item){
            $("body #settings_AddressCityList").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
          });
          $.each(data['locations'], function(index,item){
            $('body #settings_AddressCityList #'+item.place_id).append(new Option(item.address,item.id));
          });
        })
      });
      // 2 **** Add room remove Grayed out input ****// 
      $(document).on('change','#settings_AddressCityList',function(){
        $('#settings_AddressCityList').find('option').get(0).remove();
        $('#settings_rname').prop('readonly',false);
        $('#settings_etage').prop('readonly',false);
        $('#settings_altrname').prop('readonly',false);
      });
});
</script>
@endsection