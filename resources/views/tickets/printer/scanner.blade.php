@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Scanner Probleme'])
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
          <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
            @csrf
            <input type="hidden" name="problem_type" value="Scanner Probleme">
            <div class="row mx-auto">
              <!-- Submitter Section layout_ticket submitter.blade.php -->
              @include('tickets.layout_ticket.submitter')
              <!--end Submitter Section -->
              <div class="col-lg-8">
                <div class="card card-primary card-outline">
                  <div class="card-body box-profile form-group">
                    <div class="row">
                      <div class="col-md-12 d-flex justify-content-around">
                        <div class="custom-control custom-checkbox mb-3">
                          <input type="checkbox" class="custom-control-input" id="scanner_not_working" name="scanner_not_working">
                          <label class="custom-control-label" for="scanner_not_working">Scan funktioniert nicht</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                          <input type="checkbox" class="custom-control-input" id="scanner_wrong_folder" name="scanner_wrong_folder">
                          <label class="custom-control-label" for="scanner_wrong_folder">Scans nicht im Scan Ordner</label>
                        </div>
                        <!-- <div class="custom-control custom-checkbox mb-3">
                          <input type="checkbox" class="custom-control-input" id="scanner_myname_list" name="scanner_myname_list">
                          <label class="custom-control-label" for="scanner_myname_list">Scanner einrichten</label>
                        </div> -->
                      </div>
                    </div>
                  </div>
                  <div class="card-body box-profile form-group">
                    <div class="row col-md-12">
                      <div class="form-group col-md-6">
                        <label for="searchcomputer"> Welcher Rechner &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                        <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer" required>
                          <option class="form-control" value=""></option>
                          @foreach($computers as $computer)
                          <option class="form-control" value="{{$computer['id']}}">{{$computer['gname']}}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <fieldset class="border rounded px-2 mb-2">
                          <legend class="w-auto">Drucker <i class="fas fa-print"></i></legend>
                          <label for="printer_place"> Druckerstandort &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                            <select class="custom-select form-control mb-2" id="printer_place" name="printer_place" required>
                            </select>
                          <label for="printer_room"> Raum &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                            <select class="custom-select form-control mb-2" id="printer_room" name="printer_room" required>
                            </select>
                          <label for="printer_room"> Drucker &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                            <select class="custom-select form-control mb-2" id="printer_name" name="printer_name" required>
                            </select>
                        </fieldset>
                      </div>
                      @include('tickets.layout_ticket.note',['discription'=>'Beschreibung'])
                    </div>
                    <div>
                      <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                    </div>
                  </div>
                </div>
              </div> <!--end second card -->
            </div>
          </form>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
@section('script')
<script>
  $.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});

$(document).ready(function() {
let selectAddresslisten = new Array();
let roomlisten = new Array();
  $('#printer_name').find('option').remove();
  $('#printer_place').find('option').remove();
  $('#printer_place').find('optgroup').remove();
  $('#printer_place').append(new Option("Standort...",''));
  $('#printer_room').find('option').remove();
  $("#printer_room").append(new Option("Raum...",''));
  $.ajax({
    type: "get",
    url: "{{route('item.listen')}}",
    }).done(function(data) {
      selectAddresslisten = new Array();
      $.each(data['places'], function(index, item) {
          $("body #printer_place").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
      });
      $.each(data['locations'], function(index, item) {
      $("#printer_place #"+item.place_id).append(new Option(item.address,item.id));
      selectAddresslisten.push(item);
      });
    });
  $( document ).on( "change", "#printer_place", function() {
    $('#printer_name').find('option').remove();
    $('#printer_room').find('option').remove();
    $("#printer_room").append(new Option("Raum...",''));
    for(let i = 0; i<selectAddresslisten.length ; i++){
      if(selectAddresslisten[i].id == $( this ).val()){
        $.each(selectAddresslisten[i].invrooms, function(index, item) {
          $("#printer_room").append(new Option(item.rname+' ('+item.altrname+')',item.id));
          roomlisten.push(item);
          //console.log(item);
        });
      }
    }
  });

  $( document ).on( "change","#printer_room",function() {
  $.ajax({
    type:'post',
    url:"{{ route('printer_in_room') }}",
    data:{printers:$( this ).val(),location_id:$('#location_id_listen').val()},
    success:function(resp){
      $('#printer_name').find('option').remove();
          $.each(resp,function(index, item) {
          $("#printer_name").append(new Option(item.gname,item.invnr));
          });
    },error:function(){
      alert("Error");
    }
  });
});
})


</script>
@endsection




