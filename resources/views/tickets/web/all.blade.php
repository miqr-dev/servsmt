@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'WWW']) 
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
                        <!-- Terminal -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="terminal_tn">
                          <a href="{{route('terminal_tn')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/terminal_tn.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>Terminal TN</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- BigBlueButton -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100 pt-0" id="bbb">
                          <a href="#">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/bbb_400.png" id="bbb" style="max-width: 200px;" alt="" />
                                </div>
                                <div class="text-container">
                                  <h6>Big Blue Button</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- vtiger) -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="vtiger">
                          <a href="#">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                  <img src="/images/admin_images/vtiger_400.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                  <h6>vTiger</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- FirmenVZ -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="firmenvz">
                          <a href="#">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                  <img src="/images/admin_images/FirmenVZ.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">                                    
                                    <h6>Firmen VZ</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- SMT -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="smt">
                          <a href="#">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/smt2.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>SMT</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100">
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 h-100 align-self-center" id="tooltip_box_computer" >
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
let underform = $('div #tooltip_box_computer');
let rm_children = underform.children().remove();
rm_children;

  $('#terminal_tn').hover(function(){
      underform.children().remove();
      underform.append(
        `
          <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Terminal TN</h2>
                  <ul class="list-unstyled">
                    <li>Alle Software/Programm bezogenen Anfragen.</li>
                    <li>Installation, Aktualisierung, Lizenzierung etc.</li>
                  </ul>
              </div>
            </div>
          </div>
        `
      );

    },function(){
      underform.children().remove(); 
    })

  $('#bbb').hover(function(){
      underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Big Blue Button</h2>
                  <ul class="list-unstyled">
                    <li>Anfrage von Peripheriegeräten wie</li>
                    <li>Maus, Tastatur, etc. </li>
                  </ul>
              </div>
            </div>
          </div>
        `
      );

    },function(){
      underform.children().remove(); 
    })

  $('#vtiger').hover(function(){
    underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>vTiger</h2>
                  <ul class="list-unstyled">
                    <li>Anfrage von neuer Hardware wie </li>
                    <li>PC, Laptop, Bildschirm, etc.</li>
                  </ul>
              </div>
            </div>
          </div>
        `
      );

    },function(){
      underform.children().remove(); 
    })

  $('#firmenvz').hover(function(){
    underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Firmen VZ</h2>
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

  $('#smt').hover(function(){
  underform.children().remove();
    underform.append(
      `
      <div class="card-flyer2">
          <div class="text-box2">
            <div class="text-container2">
              <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
              <h2>SMT</h2>
                <ul class="list-unstyled">
                  <li>Druckerinstallation etc.</li>
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