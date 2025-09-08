@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header',['title'=>'Telefon Probleme'])

    <!-- Main content -->
  <section class="content">
    <div class="container-fluid col-lg-12">
      <div class="row">
        <div class="col-12 mx-auto">
          <div class="card card-primary card-outline">
            <div class="card-body box-profile form-group">
              <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
                @csrf
                <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                  <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                      <div id="underform">
                        <!-- ! Jquery forms here --> 
                        <input type="hidden" name="problem_type" value="Tel Probleme">
                        <div class="card-body box-profile form-group">       
                          <div class="row col-md-12">
                            <div class="form-group col-md-6">
                              <label for="tel_current_place"> Telefon standort &nbsp;<i class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="tel_current_place" name="tel_current_place" required>
                              </select>
                                <label for="tel_current_room"> Raum &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                                <select class="custom-select form-control mb-2" id="tel_current_room" name="tel_current_room" required>
                                </select>
                                <label for="searchcomputer"> Telefon &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                                <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer" required>
                                </select>
                            </div>
                              <div class="form-group col-md-6 col-lg-12">
                                <label for="notizen"> Notizen &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                                <textarea type="text" name="notizen" class="form-control notizen" required></textarea>
                              </div>
                            </div>                  
                            <div>
                              <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                            </div>
                        </div>
                        <!-- ! Jquery forms ends here -->
                      </div>
                  </div>
                </div><!--end second card -->
              </div>
            </form>
            </div><!-- /.card-body -->
          </div><!-- /.card -->
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>




@endsection

@section('script')
<script>
  $.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
})

$(document).ready(function() {
  let selectAddresslisten = new Array();
    let roomlisten = new Array();
      $('#tel_name').find('option').remove();
      $('#tel_current_place').find('option').remove();
      $('#tel_current_place').find('optgroup').remove();
      $('#tel_current_place').append(new Option("Standort...",''));
      $('#tel_current_room').find('option').remove();
      $("#tel_current_room").append(new Option("Raum...",''));
      $.ajax({
        type: "get",
        url: "{{route('item.listen')}}",
        }).done(function(data) {
          selectAddresslisten = new Array();
          $.each(data['places'], function(index, item) {
              $("body #tel_current_place").append('<optgroup label="'+index+'" id="'+item+'" ></optgroup>');
          });
          $.each(data['locations'], function(index, item) {
          $("#tel_current_place #"+item.place_id).append(new Option(item.address,item.id));
          selectAddresslisten.push(item);
          });
        });
      $( document ).on( "change", "#tel_current_place", function() {
        $('#tel_name').find('option').remove();
        $('#tel_current_room').find('option').remove();
        $("#tel_current_room").append(new Option("Raum...",''));
        for(let i = 0; i<selectAddresslisten.length ; i++){
          if(selectAddresslisten[i].id == $( this ).val()){
            $.each(selectAddresslisten[i].invrooms, function(index, item) {
              $("#tel_current_room").append(new Option(item.rname+' ('+item.altrname+')',item.id));
              roomlisten.push(item);
            });
          }
        }
      });
      
      $( document ).on( "change","#tel_current_room",function() {
      $.ajax({
        type:'post',
        url:"{{ route('tel_in_room') }}",
        data:{telephones:$( this ).val(),location_id:$('#location_id_listen').val()},
        success:function(resp){
          $('.searchcomputer').find('option').remove();
              $.each(resp,function(index, item) {
              $(".searchcomputer").append(new Option(item.gname,item.id));
              });
        },error:function(){
          alert("Error");
        }
      });
    });


    $('.notizen').summernote({
    height:150,
    lang:'de-DE'
    }); 
  })




</script>
@endsection





