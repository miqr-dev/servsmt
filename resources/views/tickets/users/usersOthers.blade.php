@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Benutzer - Sonstiges'])
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
                          <button type="button" class="btn btn-outline-primary" id="passwordChange">Anmeldeprobleme</button>
                          <button type="button" class="btn btn-outline-primary" id="usernameChange">Namensänderung</button>
                          <button type="button" class="btn btn-outline-primary" id="softother">Sonstiges</button>
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

  $('#passwordChange').click(function(){
    underform.children().remove();
    $('#passwordChange').removeClass().addClass('btn btn-primary');
    $('#usernameChange').removeClass().addClass('btn btn-outline-primary');
    $('#softother').removeClass().addClass('btn btn-outline-primary');
    underform.append(
    `
    <input type="hidden" name="problem_type" value="Anmelde Probleme">
    <div class="card-body box-profile form-group">       
      <div class="row col-md-12">
        <div class="form-group col-md-12">
          <div class="row col-md-12">
            <div class="form-group col-md-6">
              <label for="submitter">Vollständiger Name &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
              <input type="text" class="form-control" name="password_name" required>
              <input type="hidden" name="expiring_date" id="expiring_date" value="">
              </div>
            <div class="form-group col-md-6">
                <label for="searchcomputer"> Welcher Rechner </label>
                <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer">
                <option class="form-control" value="">Bitte Wählen</option>
                @foreach($computers as $computer)
                  <option class="form-control" value="{{$computer['id']}}">{{$computer['gname']}}</option>
                @endforeach
                </select>
              </div>
            </div>
              <div class="col-md-12 d-flex justify-content-around">
                <div class="custom-control custom-checkbox mb-4">
                  <input type="checkbox" class="custom-control-input abgelaufen" id="abgelaufen" name="abgelaufen">
                  <label class="custom-control-label" for="abgelaufen">Konto Abgelaufen </label>
                </div>
                <div class="custom-control custom-checkbox mb-4">
                  <input type="checkbox" class="custom-control-input inaktiv" id="inaktiv" name="inaktiv">
                  <label class="custom-control-label" for="inaktiv">Inaktives Konto</label>
                </div>
                <div class="custom-control custom-checkbox mb-4">
                  <input type="checkbox" class="custom-control-input" id="forgotten" name="forgotten">
                  <label class="custom-control-label" for="forgotten">Passwort vergessen</label>
                </div>
                <div class="custom-control custom-checkbox mb-4">
                  <input type="checkbox" class="custom-control-input" id="other_error_participant" name="other_error_participant">
                  <label class="custom-control-label" for="other_error_participant">Anderer Fehler</label>
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

    $('#abgelaufen').click(function(){
      if ($(this).prop("checked") == true){
        const { value: datum } = Swal.fire({
        title: 'Freier Mitarbeiter/in ?',
        input: 'text',
        inputLabel: 'Bitte Enddatum einfügen',
        inputPlaceholder: '',
        showCancelButton: true,
        allowOutsideClick: false,
        cancelButtonColor: '#dc3545',
        cancelButtonText: 'Kein Freie Mitarbeiter/in',
        confirmButtonColor: '#285D17',
        confirmButtonText: 'Ja, Freie Mitarbeiter/in',
        inputValidator: (value) => {
        if (!value) {
          return 'Enddatum ist für Freie Mitarbeiter / in erforderlich'
        }
      }
      })

        .then((res) => {
          console.log(res);
            if(res.value){
              console.log(res.value);
              $('#expiring_date').attr('value',res.value)
            }else if(res.dismiss == 'cancel'){
              $('#abgelaufen').prop('checked', false);
            }
            else if(res.dismiss == 'esc'){
              $('#abgelaufen').prop('checked', false);
            }
        });
    }
    });
    $(".searchcomputer").select2({
      placeholder: 'Bitte Wählen',
    });



    $('.notizen').summernote({
    height:150,
    lang:'de-DE'
    }); 
    
    
  })


  $('#usernameChange').click(function(){
    underform.children().remove();
    $('#passwordChange').removeClass().addClass('btn btn-outline-primary');
    $('#usernameChange').removeClass().addClass('btn btn-primary');
    $('#softother').removeClass().addClass('btn btn-outline-primary');
    underform.append(
      `
    <input type="hidden" name="problem_type" value="Wechsel Name">
    <div class="card-body box-profile form-group">       
      <div class="row col-md-12">
        <div class="form-group col-md-12">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="user_oldname">Alter Name &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
              <input type="text" class="form-control" name="user_oldname" required>
            </div>
            <div class="form-group col-md-6">
              <label for="user_newname">Neuer Name &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
              <input type="text" class="form-control" name="user_newname" required>
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

  $('#softother').click(function(){
    underform.children().remove();
    $('#passwordChange').removeClass().addClass('btn btn-outline-primary');
    $('#usernameChange').removeClass().addClass('btn btn-outline-primary');
    $('#softother').removeClass().addClass('btn btn-primary');
    underform.append(
      `
      <input type="hidden" name="problem_type" value="Benutzer sonstiges">
    <div class="card-body box-profile form-group">       
      <div class="row col-md-12">
        <div class="form-group col-md-6">
          <div class="row">
            <div class="form-group col-md-12">
              <label for="user_other_username">Vollständiger Name &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
              <input type="text" class="form-control" name="user_other_username" required>            
            </div>
          </div>
        </div>
        <div class="form-group col-md-6">
        </div> 
        <div class="form-group col-md-6 col-lg-12">
          <label for="notizen">Fehler Beschreibung</label>
          <textarea type="text" name="notizen" class="form-contro notizen"></textarea>
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





