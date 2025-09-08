@extends('layouts.admin_layout.admin_layout')

<style>

  /* SVG TEXTs */
  .heavy { font: bold 35px sans-serif; fill:#e8e3e3 }
  .heavyLight { font: bold 35px sans-serif; fill:#935A63 }
  .light { font: bold 35px sans-serif; fill:#06D6A0 }

  .found {
    background-color: #5cb85c;
  }
  .svg_absolute {
    position: absolute;
    z-index: 100;
    margin-bottom: 50px;
  }
  svg:hover {
  -webkit-filter: brightness(1.5);
  -moz-filter: brightness(1.5);
  -ms-filter: brightness(1.5);
  filter: brightness(1.5);
  }

  @font-face {
      font-family: 'BrzBC_Code39_MK';
      src: url('font/BrzBC_Code39_MK.woff2') format('woff2'),
            url('font/BrzBC_Code39_MK.woff') format('woff'),
            url('font/BrzBC_Code39_MK.svg#BrzBC_Code39_MK') format('svg');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
      }

  .barcode {
      font-family: 'BrzBC_Code39_MK', 'Courier', 'monospace';
      }
  .input-lg{
    font-size: 40px !important;
  }
  input[readonly].invnr_print{
  background-color:transparent;
  border: 0;
}

/* needed for effect */
.popover-content {
	display: none;
}

/* optional shadow */
.popover {
	-moz-box-shadow: 0 0 8px #5b7d83;
	-webkit-box-shadow: 0 0 8px #5b7d83;
	box-shadow: 0 0 8px #5b7d83;
}



</style>
 
@section('content')

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<!-- SubNav Bar -->
    <ul class="nav nav-pills nav-fill text-uppercase font-weight-bold">
      <li class="nav-item dropdown">
        @if(auth()->user()->can('Aktuell') || auth()->user()->can('Ausgemustert'))
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        Geräte <i class="fas fa-desktop fa-lg" style="color:#F08080;"></i></a>
        <div class="dropdown-menu w-100" aria-labelledby="navbarDropdown">
          @can('Aktuell')
          <a class="dropdown-item" href="javascript:" id="actual_list_modal">Aktuell</a>
          @endcan
          @can('Ausgemustert')
          <a class="dropdown-item" href="Javascript:" id="all_list_modal">Ausgemustert</a>
          @endcan
        </div>
        @endif
      </li>
      @can('Ändern')
      <li class="nav-item">
        <a class="nav-link" href="#" id="edit_modal">Ändern <i class="fas fa-pen-fancy fa-lg" style="color: #0275d8;"></i></a>
      </li>
      @endcan
      @if(auth()->user()->can('Erfassen_Auto') || auth()->user()->can('Erfassen_Manuell'))
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
          Erfassen <i class="fas fa-plus fa-lg" style="color:#5bc0de;"></i></a>
      @endif 
        <div class="dropdown-menu w-100" aria-labelledby="navbarDropdown">
      @can('Erfassen_Auto')
        <a class="dropdown-item" href="javascript:" id="add_modal">Erfassen</a>
      @endcan
      @can('Erfassen_Manuell')
        <a class="dropdown-item" href="Javascript:" id="add_modal_man">Manuell Erfassen</a>
      @endcan
        </div>
      </li>
      @can('Bewegen')
      <li class="nav-item">
        <a class="nav-link" href="javascript:" id="move_modal">Bewegen <i class="fas fa-expand-arrows-alt fa-lg" style="color: #5cb85c;"></i></a>
      </li>
      @endcan
      @can('Ausmustern')
      <li class="nav-item">
        <a class="nav-link" href="javascript:" id="invalid_modal" >Ausmustern <i class="far fa-times-circle fa-lg"></i></a>
      </li>
      @endcan
      @if(auth()->user()->can('Drucken_list') || auth()->user()->can('Drucken_ticket') || auth()->user()->can('Fehlende_inv'))
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="javascript:" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Drucken <i class="fas fa-print fa-lg" style="color:#007bff;"></i></a>
      @endif
        <div class="dropdown-menu w-100" aria-labelledby="navbarDropdown">
      @can('Drucken_list')
        <a class="dropdown-item" href="javascript:" id="list_modal">Listen</a>
        <div class="dropdown-divider"></div>
      @endcan
      @can('Fehlende_inv')
      <a class="dropdown-item" href="javascript:" id="print_missing">Fehlende Inv</a>
      <div class="dropdown-divider"></div>
      @endcan
      @can('Drucken_ticket')
          <a class="dropdown-item" href="javascript:" id="etiketten_modal">Etiketten</a>
        </div>
        @endcan
      </li>
      @can('Inventur')
        <li class="nav-item">
          <a class="nav-link" href="{{route('inventoryblade')}}">Inventur <i class="fas fa-dolly-flatbed fa-lg" style="color: orange;"></i></a>
          <!-- id="inventur_modal" -->
        </li>

      @endcan
      @can('umbenennen')
        <li class="nav-item">
          <a class="nav-link" href="javascript:" id="rename_modal">Umbenennen <i class="fas fa-file-signature fa-lg" style="color: red;"></i></a>
        </li>
      @endcan
    </ul>
    @can('information')
    <div class="container-fluid">
		<!-- Small boxes (Stat box) -->
      <div class="row col-md-12 mt-5">
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$computer}}</h3>
              <p>Rechner & Laptops</p>
            </div>
            <div class="icon">
              <i class="fas fa-tv"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$server}}</h3>
              <p>Server</p>
            </div>
            <div class="icon">
              <i class="fas fa-server"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$tablet}}</h3>
              <p>Tablets</p>
            </div>
            <div class="icon">
              <i class="fas fa-tablet-alt"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$printer}}</h3>
              <p>Drucker / MFC</p>
            </div>
            <div class="icon">
              <i class="fas fa-print"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$monitor}}</h3>
              <p>Monitore</p>
            </div>
            <div class="icon">
              <i class="fas fa-desktop"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$switch}}</h3>
              <p>Switch</p>
            </div>
            <div class="icon">
              <i class="far fa-hdd"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- End of First Line-->
      <!-- Second Line -->
      <div class="row col-md-12 mt-2">
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$router}}</h3>
              <p>Router</p>
            </div>
            <div class="icon">
              <i class="fas fa-hdd"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$nas}}</h3>
              <p>NAS</p>
            </div>
            <div class="icon">
              <i class="fas fa-pause"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$projector}}</h3>
              <p>Beamer</p>
            </div>
            <div class="icon">
              <i class="fas fa-film"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$tkanlage}}</h3>
              <p>TK-Anlagen</p>
            </div>
            <div class="icon">
              <i class="fas fa-tty"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$telefon}}</h3>
              <p>Telefon</p>
            </div>
            <div class="icon">
              <i class="fas fa-phone"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$scanner}}</h3>
              <p>Scanner</p>
            </div>
            <div class="icon">
              <i class="fas fa-qrcode"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
      </div>
    </div>

    <section class="container-fluid mt-3">
    <div class="row col-md-12">
      <div class="col-md-7">
        <div class="card card-primary card-outline">
          <div class="card-header p-2">
            <h3 class="card-title text-bold" style="color:red;">Fehlendes Inventar</h3>  
          </div>
          <div class="card-body">
            <table class="table table-hover table-sm text-sm" width="100%">
              <thead>
                <tr>
                  <th scope="col">Name</th>
                  <th scope="col">Standort</th>
                  <th scope="col">Raum</th>
                  <th scope="col">Wann</th>
                  <th scope="col">Wer</th>
                </tr>
              </thead>
              <tbody>
                @foreach($unordereds as $unordered)
                <tr>
                  <td style="color:red;">{{$unordered->gname}}</td>
                  <td style="color:blue;">{{$unordered->room->location->address}}</td>
                  <td style="color:blue;">{{$unordered->room->rname}} || {{$unordered->room->altrname}}</td>
                  <td style="color:green;" class="date-popover" tabindex="-1" data-toggle="popover" data-content="{{$unordered->created_at->format('H:i')}}">{{$unordered->created_at->format('d.m')}}</td>
                  <td style="color:red;">{{$unordered->who}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer p-0">
            <div class="mailbox-controls">
              <div class="float-right">
               <a href="javascript:">Alle Zeigen</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-5">
        <div class="card card-primary card-outline">
          <div class="card-header p-2">
            <h4 class="card-title text-bold" style="color:green;">Bewegungsprotokoll</h4>  
          </div>
          <div class="card-body">
            <table class="table table-hover table-sm text-sm" width="100%">
              <thead>
                <tr>
                  <th scope="col">Geräte Name</th>
                  <th scope="col">Alter Standort</th>
                  <th scope="col">Neuer Standort</th>
                  <th scope="col">Wann</th>
                </tr>
              </thead>
              <tbody>
              @foreach($movements as $movement)
                <tr>
                  <td style="color: green;"><strong>{{$movement->gname}}</strong></td>
                  <td><s style="color: red;">{{$movement->room_old->location->address}}</s></td>
                  <td style="color: blue;">{{$movement->room->location->address}}</td>
                  <td style="color: blue;">{{$movement->created_at->format('d.m')}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer p-0">
            <div class="mailbox-controls">
              <div class="float-right">
               <a href="{{route('item_move_log_index')}}">Alle Zeigen</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content for Popover -->



    </section>

    @endcan



	</div>
</section>

<!-- TODO: Modals -->
<!-- Create Modal -->
@include('inventory.create')
@include('inventory.create_man')
@include('inventory.edit')
@include('inventory.invalid')
@include('inventory.move')
@include('inventory.inventur')
@include('inventory.list')
@include('inventory.rename')
<!-- Print modal -->
@include('inventory.label')
@include('inventory.missing_label')



@endsection

@section('script')

<script>

$(function () {
  $('.date-popover').popover({
    container: 'body',
    trigger: 'focus',
  })
})

$.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});

//************************************************************* Rename *************************************************************//
$(document).on( "click", "#rename_modal", function() {
// Empty Values
$('#search_rename').val('');
$('body .submit_form_rename').attr('disabled', true)
$('body .item_rename_form').trigger('reset')
$("body .pdf_rename_red").hide();								
$("body .pdf_rename_green").hide();
$("body #chksrchrename").removeClass().addClass('fas fa-ellipsis-h').css('color','#0275d8');
$(".modal-header").removeClass('found');
$('#rename').modal('show');
$(document).on('keyup change', '#search_rename', function(){
let search_rename = $(this).val();
$.ajax({
  headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
  type:'post',
  url:"{{ route('search_check_rename') }}",
  data:{search_rename:search_rename},
  success:function(resp){
    if(resp=="false"){
      $("body #chksrchrename").removeClass().addClass('fas fa-times-circle').css('color', '#d9534f');
      $('body .submit_form_rename').attr('disabled', true)
      $('body .item_rename_form').trigger('reset');
      $(".modal-header").removeClass('found');
      $("body .pdf_rename_red").hide();								
      $("body .pdf_rename_green").hide();
    }else{
      if(resp =="true") {
        $("body #chksrchrename").removeClass().addClass('fas fa-check').css('color', '#5cb85c');
        $('body .submit_form_rename').attr('disabled', false)
        $(".modal-header").addClass('found')
        $("body .pdf_rename_green").hide();
        $("body .pdf_rename_red").show();
        $.ajax({
          type:'get',
          url:"{{ route('search_rename') }}",
          data:{search_rename:search_rename},
          success:function(resp){
            if(resp.items.path_to_rg) {
              $("body .pdf_rename_green").show();
              $("body .pdf_rename_red").hide();
              $("body .pdf_rename_green").attr("href", "/inventar/rechnungen/"+resp.items.path_to_rg);
            }
            $('body .item_rename_form .invnr_rename').val(resp.items.invnr)
            $('body .item_rename_form .gname_rename').val(resp.items.gname)
            $('body .item_rename_form .andat_rename').val(resp.items.andat)
            $('body .item_rename_form .kp_rename').val(resp.items.kp)
            $('body .item_rename_form .standort_rename').val(resp.room.invroom.location.address)
            $('body .item_rename_form .raum_rename').val(resp.room.invroom.rname)
            $('body .item_rename_form .gart_rename').val(resp.items.garts.name)
            $('body .item_rename_form .gtyp_rename').val(resp.items.gtyp)
            $('body .item_rename_form .sn_rename').val(resp.items.sn)
            $('body .item_rename_form .notes_rename').val(resp.items.notes)
          }
          // ,error:function(){
          //   alert("Error");
          // }
        });
      }
    }
  },error:function(){
    alert("Error");
  }
});
});
});
//************************************************************* Edit ***************************************************************//
$(document).on( "click", "#edit_modal", function() {
// Empty Values
$('#search_edit').val('');
$('body .item_edit_form').trigger('reset')
$("body .pdf_edit_red").hide();								
$("body .pdf_edit_green").hide();
$("body #chksrchedit").removeClass().addClass('fas fa-ellipsis-h').css('color','#0275d8');
$(".modal-header").removeClass('found');
$('#edit').modal('show');
$(document).on('keyup change', '#search_edit', function(){
	let search_edit = $(this).val();
	$.ajax({
		type:'post',
		url:"{{ route('search_check_edit') }}",
		data:{search_edit:search_edit},
		success:function(resp){
			if(resp=="false"){
				$("body #chksrchedit").removeClass().addClass('fas fa-times-circle').css('color', '#d9534f');
        $('body .item_edit_form').trigger('reset');
        $(".modal-header").removeClass('found');
        $("body .pdf_edit_red").hide();								
        $("body .pdf_edit_green").hide();
			}else{
				if(resp =="true") {
                $("body #chksrchedit").removeClass().addClass('fas fa-check').css('color', '#5cb85c');
                $(".modal-header").addClass('found')
                $("body .pdf_edit_green").hide();
                $("body .pdf_edit_red").show();
					$.ajax({
						type:'get',
						url:"{{ route('search_edit') }}",
						data:{search_edit:search_edit},
						success:function(resp){
							if(resp.items.path_to_rg) {
								$("body .pdf_edit_green").show();
								$("body .pdf_edit_red").hide();
								$("body .pdf_edit_green").attr("href", "/inventar/rechnungen/"+resp.items.path_to_rg);
							}
							$('body .item_edit_form .invnr_edit').val(resp.items.invnr)
							$('body .item_edit_form .invnr_id').val(resp.items.id)
							$('body .item_edit_form .gname_edit').val(resp.items.gname)
							$('body .item_edit_form .andat_edit').val(resp.items.andat)
							$('body .item_edit_form .kp_edit').val(resp.items.kp)
							$('body .item_edit_form .standort_edit').val(resp.room.invroom.location.address)
              let $rname = resp.room.invroom.rname;
              let $altrname = resp.room.invroom.altrname;
							$('body .item_edit_form .raum_edit').val($rname +' - '+ $altrname)
							$('body .item_edit_form .gart_edit').val(resp.items.garts.name)
							$('body .item_edit_form .gtyp_edit').val(resp.items.gtyp)
							$('body .item_edit_form .sn_edit').val(resp.items.sn)
							$('body .item_edit_form .notes_edit').val(resp.items.notes)
							$('body .item_edit_form .invnr_print').val(resp.items.invnr)
						},error:function(){
							alert("Error");
						}
					});
				}
			}
		},error:function(){
			alert("Error");
		}
	});
});
});

//************************************************************* Create *************************************************************//
$( document ).on( "click", "#add_modal", function() {
  $('#add').modal('show');
  //empty form on open
  $('.modal-header').removeClass('found');
  $('body .kaufpreis').val('');
  $('body .gname').val('');
  myDropzone.removeAllFiles();
  $('body .gtyp').val('');
  $('body .sn').val('');
  $('body .notes').val('');
  $('#location_id').find('optgroup').remove();
  $('#location_id').find('option').remove();
  $('#rooms').find('option').remove();
  $('#gart_id').find('option').remove();
  $('#location_id').append(new Option("Standort...",''));
  $('#rooms').append(new Option("Raum...",''));
  $('#gart_id').append(new Option("Geräteart...",''));
  $('.invnr').val('');
  //end empty form //
  //*******  save the City name, to sort the addresses below each city.  ********//
  locationData = new Array();
  $.ajax({
    type: "GET",
    url: "{{ route('item.create') }}",
    }).done(function( data ) {
      $.each(data['places'], function(index, item) {
          $("#location_id").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
      });
      $.each(data['invnr'], function(index, item) {
          $("#location_id #"+item.location.place_id).append(new Option(item.location.address, item.location_id));
          locationData.push(item);
      });
      $.each(data['garts'], function(index, item) {
          $("#gart_id").append(new Option(item.name, item.id));
      });
    });
});
$( document ).on( "change", "#location_id", function() {
  $('#location_id').append(new Option("Standort...",''));
  $('#rooms').find('option').remove();
  $("#rooms").append(new Option("Raum Wählen...",''));
  for(let i = 0; i<locationData.length ; i++){
    if(locationData[i].location_id == $( this ).val()){
      texty = locationData[i].location_id + '-' + (parseInt(locationData[i].last_inv_num) + 1) + '-' + locationData[i].suffix;
      $.each(locationData[i].room, function(index, item) {
        $("#rooms").append(new Option(item.rname+' ('+item.altrname+')',item.id));
      });
    }
  }
  $('.invnr').val(texty);
});
$(function() {
  $('.date').daterangepicker({
		singleDatePicker: true,
		showDropdowns: true,
		minYear: parseInt(moment().format('YYYY'))-1,
		maxYear: parseInt(moment().format('YYYY'))+1,
		locale: {
			format: 'YYYY-MM-DD'
		}
  });
});
//************************************************************* Man. Create ********************************************************//
let locationSelect = new Array();
$(document).on( "click", "#add_modal_man", function() {
	// empty form on open function
	$('#add_man').modal('show');
  $('.modal-header').removeClass('found');
	$('#location_id_man').find('option').remove();
	$('#location_id_man').find('optgroup').remove();
	locationSelect = new Array();
	$('#gart_id_man').find('option').remove();
	$('#gart_id_man').append(new Option("Geräteart...",0));
	$('#location_id_man').append(new Option("Standort...",0));
	$('#rooms_id_man').find('option').remove();
	$("#rooms_id_man").append(new Option("Raum...",''));
$.ajax({
	type: "GET",
	url: "{{ route('item.create_man') }}",
	}).done(function(data) {
		$.each(data['places'], function(index, item) {
			$("#location_id_man").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
		});
		$.each(data['locations'], function(index, item){
			$("#location_id_man #"+item.place_id).append(new Option(item.address,item.id));
			locationSelect.push(item);
		});
		$.each(data['garts'], function(idex,item){
			$("#gart_id_man").append(new Option(item.name,item.id));
		})
	});
});
$( document ).on( "change", "#location_id_man", function() {
	$('#rooms_man').find('option').remove();
	$("#rooms_man").append(new Option("Raum...",''));
	for(let i = 0; i<locationSelect.length ; i++){
		if(locationSelect[i].id == $( this ).val()){
			$.each(locationSelect[i].invrooms, function(index, item) {
					$("#rooms_man").append(new Option(item.rname+' ('+item.altrname+')',item.id));
				});
			}
		}
	});
$(function() {
  $('.date_man').daterangepicker({
	singleDatePicker: true,
	showDropdowns: true,
	minYear: parseInt(moment().format('YYYY'))-1,
	maxYear: parseInt(moment().format('YYYY'))+1,
	locale: {
	  format: 'YYYY-MM-DD'
	}
  });
});
  //************************************************************* Print Label ******************************************************//
  let locationData = new Array();
  let text = '';
  $( document ).on( "click", "#etiketten_modal", function() {
    $('.modal-header').removeClass('found');
    $('#printpage').modal('show');
    $('#anzahl').val(1);
    $('#address').find('option').remove();
    $('#address').find('optgroup').remove();
    $("#address").append(new Option("Bitte Wählen...",0));
    $('.inventNumber').val('');
    locationData = new Array();
    $.ajax({
      type: "GET",
      url: "{{ route('auto') }}", //Route NAME USED
      }).done(function( data ) {
        $.each(data['places'], function(index, item) {
					$("body #address").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
			});
      $.each(data['lastNumber'], function(index, item) {
			$("#address #"+item.location.place_id).append(new Option(item.location.address,item.location.id));
			locationData.push(item);
			});
    });
  });
  $( document ).on( "change", "#address", function() {
    for(let i = 0; i<locationData.length ; i++){
      if(locationData[i].location_id == $( this ).val()){
        texty = locationData[i].location_id + '-' + (parseInt(locationData[i].last_inv_num) + 1) + '-' + locationData[i].suffix;
      }
    }
    $('.inventNumber').val(texty);
  });
  //********************************************************** Print Etiketten ******************************************************//
  function printfunction() {
    $('#printpage').modal('show');
    $('.modal-header').removeClass('found');
    let printinvnr = $('#prntinvnr').val();
    let anzahl = $('#anzahl').val();
    let WinPrint = window.open('/print/'+printinvnr+'/'+anzahl, '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    setInterval(function(){ WinPrint.close()}, 3000);
	}

   //************************************************************* Print Missing Label ******************************************************//
  $( document ).on( "click", "#print_missing", function() {
    $('#missing_label').modal('show');
    $.ajax({
      type: "get",
      url: "{{ route('search_missing')}}", 
      }).done(function( data ) {
        $.each(data['gerate'], function(index, item) {
          $("body #searchgerate").append(new Option(item['gname'],item['invnr']))  
        });
			});

      $( document ).on( "change", "#searchgerate", function() {
       let missing_printer =  $("body #searchgerate").val()
      });
      

      $(".searchgerate").select2({
        width: "100%"
    });

     
	});
  //********************************************************** Print Missing page ******************************************************//
  function printmissingfunction() {
    $('#print_missing').modal('show');
    let printinvnr = $('#searchgerate').val();
    let WinPrint = window.open('/printmissing/'+printinvnr, '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    // setInterval(function(){ WinPrint.close()}, 3000);
	}
//************************************************************* List Print **********************************************************//
let selectAddresslisten = new Array();
let roomlisten = new Array();
$(document).on("click", "#list_modal", function() {
	$('#listen').modal('show');
  $('.modal-header').removeClass('found');
	$('#location_id_listen').find('option').remove();
	$('#location_id_listen').find('optgroup').remove();
	$('#location_id_listen').append(new Option("Standort...",''));
	$('#rooms_id_listen').find('option').remove();
	$("#rooms_id_listen").append(new Option("Raum...",''));
	$.ajax({
		type: "get",
		url: "{{route('item.listen')}}",
		}).done(function(data) {
			selectAddresslisten = new Array();
			$.each(data['places'], function(index, item) {
					$("body #location_id_listen").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
			});
			$.each(data['locations'], function(index, item) {
			$("#location_id_listen #"+item.place_id).append(new Option(item.address,item.id));
			selectAddresslisten.push(item);
			});
		});
});
$( document ).on( "change", "#location_id_listen", function() {
	$('#room_id_listen').find('option').remove();
	$("#room_id_listen").append(new Option("Raum...",''));
	for(let i = 0; i<selectAddresslisten.length ; i++){
		if(selectAddresslisten[i].id == $( this ).val()){
			$.each(selectAddresslisten[i].invrooms, function(index, item) {
				$("body #room_id_listen").append(new Option(item.rname+' ('+item.altrname+')',item.id));
				roomlisten.push(item);
			});
		}
	}
});
$( document ).on( "change","#room_id_listen",function() {
	$.ajax({
		type:'post',
		url:"{{ route('items_in_room_listen') }}",
		data:{room_id:$( this ).val(),location_id:$('#location_id_listen').val()},
		success:function(resp){
			$('body #table_listen').show();
      $('body #table_listen tbody').empty();
      $('body #altrname_listen').append()
				$.each(resp, function(index, item) {
                    let gtyp = item.gtyp ? item.gtyp : '-';
                    $('body #table_listen tbody').append('<tr id="'+item.invnr+'"><td>'+(index+1)+'</td><td>'+item.invnr+'</td><td>'+item.gname+
                        '</td><td>'+item.garts.name+'</td><td>'+gtyp+'</td></tr>')
			    });
		},error:function(){
			alert("Error");
		}
	});
});
$(document).on("click","#print_list",function(){
  $('.modal-header').removeClass('found');
		$("body .print_div").text("Raum:" +$("body #room_id_listen :selected").text()+' '+'Address: '+$("body #location_id_listen :selected").text());
    $('body #listen .modal-body').print();
});
$.fn.extend({
	print: function() {
		var frameName = 'printIframe';
		var doc = window.frames[frameName];
		if (!doc) {
			$('<iframe>').hide().attr('name', frameName).appendTo(document.body);
			doc = window.frames[frameName];
		}
		doc.document.body.innerHTML = this.html();
		doc.window.print();
		return this;
	}
});

//*********************** Inventur ********************//

// zuordnen null  = ist / soll
// zuordnen 0     = Fehlendes Inventar
// zuordnen 1     = Hinzufügen

let itemList = new Array();
$( document ).on( "change","body #inventur_check_input",function() {
	let found = false;
	$.each(itemList, function(index, item) {
		if(item.invnr == $('body #inventur_check_input').val())
		{
			item.zuordnen=null;  //found 
			$('body .table_inventur #'+$('body #inventur_check_input').val()).parents().eq(1).hide();
			$('body #inventur_check_input').val('');
			$( "body #inventur_check_input" ).focus();
			found = true;
		}
	});
	if (found == false){
		$.ajax({
			type: "get",
			url: "{!! route('getinvnr') !!}/"+$('body #inventur_check_input').val(),
			}).done(function(item) {
					//$('body #table_inventur tbody').append('<tr id="'+item.invnr+'"><td>'+((++globalIndex)+1)+'</td><td>'+item.invnr+'</td><td>'+item.gname+'</td><td>'+
					// '<label class="toggle"><input class="toggle-checkbox" name="zuordnen'+item.invnr+'" value="yes" type="checkbox" checked="checked"><div class="toggle-switch"></div><span class="toggle-label"></span></label>'
					//'</td></tr>')
          $('body .table_inventur').append(`
              <div class="col-lg-3 col-md-3 col-sm-12  ">
                <div class="m-2 p-2">
                  <svg viewBox="0 0 460.2 460.2" id="${item.invnr}" onclick="sendInfo('${item.invroom.id}','${item.invroom.ad_ou}','${item.gname}','${item.invnr}')" style="enable-background:new 0 0 460.233 460.233" xml:space="preserve">
                    <path style="fill:#d8d9dd" d="M309.1 441.5H151.3l19.8-89.5h118.3z"/><path style="fill:#d8d9dd" d="M131.6 431.3h197.2v18.3H131.6z"/><path style="fill:#59595a" d="m439.7 75.5.2.2v256H20.6v-256l.1-.2h419zm20.5 128.1v-128c0-10.8-9.6-20.4-20.5-20.4h-419A21 21 0 0 0 .2 75.7v255.9A21 21 0 0 0 20.7 352h419c10.9 0 20.5-9.6 20.5-20.4v-128z"/><path style="fill:#661421" d="m439.7 75.5.2.2v256H20.6v-256l.1-.2h419z"/><path style="opacity:.4;fill:#5b5b5f;enable-background:new" d="M131.6 431.3h197.2v18.3H131.6z"/><path style="opacity:.2;fill:#fff;enable-background:new" d="M180.3 362.1 153 374.2 0 27l27.3-12zM232.3 357.8l-27.3 12L52 22.7l27.3-12.1z"/>
                  <text x="15%" y="25%" fill="#fff" font-weight="bold" font-size="30px">${item.gname}</text>
                  <text x="35%" y="65%" fill="#81ea1c" font-weight="bold" font-size="30px">${item.invnr}</text>
                  </svg>
                </div>
              </div>
          `)
					itemList.push({ 
            invnr:item.invnr,
            gname:item.gname,
            gart_id:item.gart_id,
            place:item.invroom.location.place.pnname,
            address:item.invroom.location.address,
            location_id:item.invroom.location.id,
            room_id_old:item.invroom.id,
            room_id_new:$("#room_id_inventur").val(),
            room:item.invroom.rname,
            ad_ou:item.invroom.ad_ou,
            zuordnen:1 //add 
					});
				$('body #inventur_check_input').val('');
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type:'post',
            url:"{{ route('autoChangeLocation') }}",
            data:itemList[itemList.length-1],
            success:function(resp){
            },error:function(){
              alert("Error");
            }
          });
				$("body #inventur_check_input").focus();
			});
		}
	});
  
$( document ).on( "click","body #inventur_submit",function() {  
    $.ajax({
			type:'post',
			url:"{{ route('inventurStoreFinal') }}",
			data:{'itemList':itemList,location_id:$('#location_id_inventur').val(),room_id:$('#room_id_inventur').val()},
			success:function(resp){
      console.log(resp);
			let WinPrint = window.open('/print_inventur?val='+JSON.stringify(resp),'', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
			WinPrint.document.close();
			WinPrint.focus();
			WinPrint.print();
			setInterval(function(){ WinPrint.close()}, 3000);
			},error:function(){
					alert("Error");
			}
    });
});
//************************************************************* Bewegen ************************************************************//
	let selectAddress = new Array();
	$( document ).on( "click", "#move_modal", function() {
    $('body .submit_form_move').attr('disabled', true)
		$('#move').modal('show');
	// Empty Values
		$('#search_move').val('');
		$('body .move_form').trigger('reset')
    $(".modal-header").removeClass('found');
	$(document).on('keyup change', '#search_move', function(){
		let search_move = $(this).val();
		$.ajax({
			type:'post',
			url:"{{ route('search_check_move') }}",
			data:{search_move:search_move},
			success:function(resp){
				if(resp=="false"){
					$("body #chksrchmove").removeClass('fas fa-ellipsis-h').addClass('fas fa-times-circle').css('color', '#d9534f');
          $('body .move_form').trigger('reset')
          $(".modal-header").removeClass('found');
				}else{
					if(resp =="true") {
						$("body #chksrchmove").removeClass('fas fa-times-circle').addClass('fas fa-check').css('color', '#5cb85c');
            $(".modal-header").addClass('found');
						$.ajax({
							type: 'get',
							url:"{{ route('search_move') }}",
							data: {search_move:search_move},
							success:function(resp){
								selectAddress = new Array();
								$('#location_id_move').find('optgroup').remove();
								$('#location_id_move').find('option').remove();
								$('#location_id_move').append(new Option("Standort...",0));
								$('body .move_form .move_address').val(resp.items.invroom.location.address)
								$('body .move_form .move_raum').val(resp.items.invroom.rname)
								$('body .move_form .old_room_id_move').val(resp.items.invroom.id)
								$('body .move_form .gname_move').val(resp.items.gname)
								//$('body .move_form .ad_ou').val(resp.items.invroom.ad_ou)
								$.each(resp['places'], function(index, item) {
								$("body #location_id_move").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
								});
								$.each(resp['locations'], function(index, item) {
								$("#location_id_move #"+item.place_id).append(new Option(item.address,item.id));
								selectAddress.push(item);
								});
							},error:function(){
								alert("Error");
							}
						});
					}
				}
			}
		});
	});
});
$( document ).on( "change", "#location_id_move", function() {
	$('#room_id_move').find('option').remove();
	$("#room_id_move").append(new Option("Raum...",''));
	for(let i = 0; i<selectAddress.length ; i++){
		if(selectAddress[i].id == $( this ).val()){
			$.each(selectAddress[i].invrooms, function(index, item) {
				$("body #room_id_move").append(new Option(item.rname+' ('+item.altrname+')',item.id));
        //$('body .move_form .ad_ou').$( this ).val();
			});
		}
  }
});
$( document ).on("change","#room_id_move", function(){
  $('body .submit_form_move').attr('disabled', false)
  // $('body .move_form .ad_ou').val($("#room_id_move").val());
});
//************************************************************* Ausmuster **********************************************************//
$(document).ready(function(){
$(document).on( "click", "#invalid_modal", function() {
  // Empty Values
$('#search_amg').val('');
$('body .amg_form').trigger('reset');
$('.modal-header').removeClass('found');
$("body #chksrch").removeClass().addClass('fas fa-ellipsis-h').css('color', '#0275d8');
$('#invalid').modal('show');
});
$(document).on('keyup change', '#search_amg', function(){
  let search = $(this).val();
	$.ajax({
		type:'post',
		url:"{{ route('search_check') }}",
		data:{search:search},
		success:function(resp){
			if(resp=="false"){
				$("body #chksrch").removeClass().addClass('fas fa-times-circle').css('color', '#d9534f');
        $('.modal-header').removeClass('found');
        $('body .amg_form').trigger('reset')
			}else{
				if(resp =="true") {
					$("body #chksrch").removeClass().addClass('fas fa-check').css('color', '#5cb85c');
          $('.modal-header').addClass('found');
					$.ajax({
						type:'get',
						url:"{{ route('search') }}",
						data:{search:search},
						success:function(resp){
							$('body .amg_form .gname_amg').val(resp.items.gname)
							$('body .amg_form .inventarnummer_amg').val(resp.items.invnr)
							$('body .amg_form .andat_amg').val(resp.items.andat)
							$('body .amg_form .kp_amg').val(resp.items.kp)
							$('body .amg_form .standort_amg').val(resp.room.invroom.location.address)
              let $rname = resp.room.invroom.rname;
              let $altrname = resp.room.invroom.altrname;
							$('body .amg_form .raum_amg').val($rname +' - '+ $altrname)
							$('body .amg_form .gart_amg').val(resp.items.garts.name)
							$('body .amg_form .gtyp_amg').val(resp.items.gtyp)
							$('body .amg_form .sn_amg').val(resp.items.sn)
							$('body .amg_form .notes_amg').val(resp.items.notes)
							$('body #grund').find('option').remove();
							$('body #grund').append(new Option("Grund...",''));
							$.each(resp.amgs, function(index, item){
								$("body #grund").append(new Option(item.name,item.id));
							});
						},error:function(){
							alert("Error");
						}
					});
				}
			}
		},error:function(){
			alert("Error");
		}
  });
});
});
// drop zone
Dropzone.options.dropzoneForm = {
	autoProcessQueue : false,
	acceptedFiles : ".pdf",
  maxFilesize: 4,
	maxFiles:1,
  addRemoveLinks: true,
	init:function(){
	  var submitButton = document.querySelector(".submit_form_ajax");
	  myDropzone = this;
	  submitButton.addEventListener('click', function(){
		myDropzone.processQueue();
	  });
	  this.on("addedfile", function(data){
			$('.submit_form_ajax').css('visibility','visible');
			$('.submit_form').css('visibility','hidden');
	  });
    this.on("maxfilesexceeded", function() {
      if (this.files[1]!=null){
        this.removeFile(this.files[0]);
      }
    }),
	  this.on("complete", function(data){
		$('.path_to_rg').val(data.xhr.response);
		// load_images();
		$('#item_form').submit();
    myDropzone.removeFile(data);
	  });

	}
  };
//   load_images();
  function load_images()
  {
	$.ajax({
	  url:"{{ route('dropzone.fetch_pdf') }}",
	  success:function(data)
	  {
		$('#uploaded_pdf').html(data);
	  }
	})
  }
  $(document).on('click', '.remove_pdf', function(){
	var name = $(this).attr('id');
	$.ajax({
	  url:"{{ route('dropzone.delete_pdf') }}",
	  data:{name : name},
	  success:function(data){
		load_images();
	  }
	})
  });
// drop zone man
Dropzone.options.dropzoneFormMan = {
	autoProcessQueue : false,
	acceptedFiles : ".pdf",
  maxFilesize: 5,
	maxFiles:1,
	init:function(){
	  var submitButton = document.querySelector(".submit_form_ajax_man");
	  myDropzoneMan = this;
	  submitButton.addEventListener('click', function(){
		myDropzoneMan.processQueue();
	  });
	  this.on("addedfile", function(data){
//          console.log('sdklfjösdalkjf');
		  $('.submit_form_ajax_man').css('visibility','visible');
		  $('.submit_form_man').css('visibility','hidden');
	});
	this.on("complete", function(data){
	  $('.path_to_rg_man').val(data.xhr.response);
	  // load_images();
	  $('#item_form_man').submit();
	});
  }
};
//   load_images();
  function load_images()
  {
	$.ajax({
	  url:"{{ route('dropzone.fetch_pdf_man') }}",
	  success:function(data)
	  {
		$('#uploaded_pdf_man').html(data);
	  }
	})
  }
  $(document).on('click', '.remove_pdf', function(){
	var name = $(this).attr('id');
	$.ajax({
	  url:"{{ route('dropzone.delete_pdf_man') }}",
	  data:{name : name},
	  success:function(data){
		load_images();
	  }
	})
  });
//************************************************************* Dropzone ************************************************************//
  Dropzone.prototype.defaultOptions.dictDefaultMessage = "Legen Sie die PDF-Datei hier ab, um sie hochzuladen";
  Dropzone.prototype.defaultOptions.dictFallbackMessage = "Ihr Browser unterstützt Drag&Drop Dateiuploads nicht";
  Dropzone.prototype.defaultOptions.dictFallbackText = "Benutzen Sie das Formular um Ihre Dateien hochzuladen";
  Dropzone.prototype.defaultOptions.dictInvalidFileType = "Eine Datei dieses Typs kann nicht hochgeladen werden";
  Dropzone.prototype.defaultOptions.dictCancelUpload = "Hochladen abbrechen";
  Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "null";
  Dropzone.prototype.defaultOptions.dictRemoveFile = "Datei entfernen";
  Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "Sie können keine weiteren Dateien mehr hochladen.";
</script>
@endsection



