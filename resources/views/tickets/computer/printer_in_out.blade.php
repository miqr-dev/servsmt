@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Druckeranfrage'])
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid col-lg-12">
      <div class="row">
        <div class="col-12 mx-auto">
          <!-- Profile Image -->
          <div class="card card-primary card-outline">
            <div class="card-body box-profile form-group">
              <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
                @csrf
                <!-- child cards -->
                <div class="row mx-auto">
                  <!-- Submitter Section layout_ticket submitter.blade.php -->
                  @include('tickets.layout_ticket.submitter')
                  <!--end Submitter Section -->
                  <!-- second card -->
                  <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                      <div id="underform">
                        <input type="hidden" name="problem_type" value="Drucker Einrichten">
                        <div class="card-body box-profile form-group">       
                          <div class="row col-md-12">
                            <div class="form-group col-md-6">
                              <label for="searchcomputer"> Welcher Rechner &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                              <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer" required>
                              <option class="form-control" value="">Bitte wählen</option>
                              @foreach($computers as $computer)
                                <option class="form-control" value="{{$computer['id']}}">{{$computer['gname']}}</option>
                              @endforeach
                              </select>
                              <br><br><br>
                              <!-- <div class="card-flyer2 text-center">
                                <div class="text-box2">
                                  <div class="text-container2">
                                    <img src="/images/admin_images/info_400_2.png" style="max-width:70px;"  alt="" />
                                      <p class="text-info">Klicken Sie<a href="#"> hier</a>, falls der Drucker nicht aufgelistet ist.</p>
                                  </div>
                                </div>
                              </div> -->
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
                    </div>
                  </div>
                  <!--end second card -->
              </div>
            </form>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
    <!-- /.content -->
  <!-- /.content-wrapper -->



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

$(document).on("change", "#printer_room", function() {
    $.ajax({
        type: 'post',
        url: "{{ route('printer_in_room') }}",
        data: {printers: $(this).val(), location_id: $('#location_id_listen').val()},
        success: function(resp) {
            $('#printer_name').find('option').remove();
            if (resp.length === 0) {
                // If no printers are found, add the "Nicht aufgeführt" option
                $("#printer_name").append(new Option("Nicht aufgeführt", "not_listed"));
            } else {
                $.each(resp, function(index, item) {
                    $("#printer_name").append(new Option(item.gname, item.invnr));
                });
            }
        },
        error: function() {
            alert("Error");
        }
    });
});
  
    $(".searchcomputer").select2({
    });

    $('.searchsoftware').select2({
      placeholder: 'sometext',
      allowClear: false,
      tags: true
    });
  })

</script>
@endsection





