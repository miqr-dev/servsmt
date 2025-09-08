@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'FirmenVZ'])
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
                    <div class="card-body box-profile form-group">
                      <div class="row">
                        <div class="col-md-12 d-flex justify-content-around">
                          <button type="button" class="btn btn-outline-primary" id="firmen_requests">Funktionsanfragen</button>
                          <button type="button" class="btn btn-outline-primary" id="firmen_errors">Fehlermeldung</button>
                        </div>
                      </div>
                    </div>
                    <div id="underform">
                      <!-- ! Jquery forms here --> 
                      <!-- ! Jquery forms ends here -->
                    </div>
                  </div>
                </div><!--end second card -->
              </div>
            </form>
            </div><!-- /.card-body -->
          </div><!-- /.card -->
        </div> <!-- /.col -->
      </div> <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section><!-- /.content -->



@endsection

@section('script')
<script>
  $.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});

$(document).ready(function() {
  let underform = $('div#underform');
  let rm_children = underform.children().remove();
  rm_children;

  $('#firmen_requests').click(function(){
    underform.children().remove();
    $('#firmen_requests').removeClass().addClass('btn btn-primary');
    $('#firmen_errors').removeClass().addClass('btn btn-outline-primary');
    underform.append(
    `
    <input type="hidden" name="problem_type" value="FirmenVZ Funktionsanfragen">
    <div class="card-body box-profile form-group">       
      <div class="row col-md-12">
        <div class="form-group col-md-12">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="firmen_subject">Betreff &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
              <input type="text" class="form-control" name="firmen_subject" required>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group col-md-6">
        </div> 
        <div class="form-group col-md-6 col-lg-12">
          <label for="notizen">Beschreibung</label>
          <textarea type="text" name="notizen" class="form-contro notizen"></textarea>
        </div>
        <div>
          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
        </div>
      </div>
    </div> 
    `
    );
    $('.notizen').summernote({
    height:150,
    lang:'de-DE'
    }); 
    
    
  })


  $('#firmen_errors').click(function(){
    underform.children().remove();
    $('#firmen_requests').removeClass().addClass('btn btn-outline-primary');
    $('#firmen_errors').removeClass().addClass('btn btn-primary');
    underform.append(
      `
    <input type="hidden" name="problem_type" value="FirmenVZ Fehlermeldung">
    <div class="card-body box-profile form-group">       
      <div class="row col-md-12">
        <div class="form-group col-md-12">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="firmen_subject">Betreff &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
              <input type="text" class="form-control" name="firmen_subject" required>
            </div>
            <div class="form-group col-md-6">
              <label for="firmen_username">Benutzername</label>
              <input type="text" class="form-control" name="firmen_username">
            </div>
          </div>
        </div>
        <div class="form-group col-md-6">
        </div> 
        <div class="form-group col-md-6 col-lg-12">
          <label for="notizen">Beschreibung</label>
          <textarea type="text" name="notizen" class="form-contro notizen" ></textarea>
        </div>
        </div>                  
        <div>
          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
        </div>
      </div>
      `
    );
    $('.notizen').summernote({
    height:150,
    lang:'de-DE'
    });  

  })

});

</script>
@endsection





