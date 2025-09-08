@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Benutzer']) 
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <div class="row mx-auto justify-content-center">
              <div class="col-lg-12">
                <div class="card card-primary card-outline" id="underform">
                  <div id="cards_landscape_wrap-2">
                    <div class="container">
                      <div class="row">
                        <!-- Neuer Mitarbeiter -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_employee">
                          <a href="{{route('users_employee')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/mitarbeiter.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>Neuer Mitarbeiter</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- Neuer Benutzer -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100 pt-0" id="info_newuser">
                          <a href="{{route('users_participant')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/teilnehmer.png" style="max-width: 200px;" alt="" />
                                </div>
                                <div class="text-container">
                                  <h6>Neuer Teilnehmer</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- Sonstiges -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100 mb-5" id="users_sonstiges"><!-- ? margin button on the last element-->
                          <a href="{{route('users_others')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/sonstigers_users.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>Sonstiges</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 h-100 align-self-center" id="tooltip_box_users" >
                        <!-- ! Jquery forms here --> 
                        <!-- ! Jquery forms ends here -->
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- row mx-auto justify content center-->
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div><!-- /.col-12 mx-auto -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
@endsection
@section('script')
<script>
$(document).ready(function() {
let underform = $('div #tooltip_box_users');
let rm_children = underform.children().remove();
rm_children;

  $('#info_employee').hover(function(){
      underform.children().remove();
      underform.append(
        `
          <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Neuer Mitarbeiter.</h2>
                  <ul class="list-unstyled">
                    <li>Benutzeranfrage für neue Mitarbeiter.</li>
                  </ul>
              </div>
            </div>
          </div>
        `
      );

    },function(){
      underform.children().remove(); 
    })

  $('#info_newuser').hover(function(){
      underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Neuer Teilnehmer</h2>
                  <ul class="list-unstyled">
                    <li>Benutzeranfrage für neue Teilnehmer</li>
                  </ul>
              </div>
            </div>
          </div>
        `
      );

    },function(){
      underform.children().remove(); 
    })

  $('#users_passwordchange').hover(function(){
    underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Neues Passwort <i class="fas fa-unlock-alt"></i></h2>
                  <ul class="list-unstyled">
                  </ul>
              </div>
            </div>
          </div>
        `
      );

    },function(){
      underform.children().remove(); 
    })

  $('#users_changename').hover(function(){
    underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Probleme</h2>
                  <ul class="list-unstyled">
                    <li>Alle Arten von PC Problemen.</li>
                    <li>Defekt, Netzwerkausfall, Peripherie..</li>
                  </ul>
              </div>
            </div>
          </div>
        `
      );
    },function(){
      underform.children().remove(); 
    })

  $('#users_sonstiges').hover(function(){
  underform.children().remove();
    underform.append(
      `
      <div class="card-flyer2">
          <div class="text-box2">
            <div class="text-container2">
              <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
              <h2>Sonstiges</h2>
                <ul class="list-unstyled">
                  <li>Neues Passwort, Namensänderung, Anmeldeprobleme</li>
                  <br>
                </ul>
            </div>
          </div>
        </div>
      `
    );

  },function(){
    underform.children().remove(); 
  })

  })

</script>
@endsection