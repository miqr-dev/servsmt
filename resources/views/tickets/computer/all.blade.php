@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'PC / Laptop']) 
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
                        <!-- PC-Probleme -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_problems">
                          <a href="{{route('pc_problems')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/course_300.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>PC-Probleme</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- Softwareanfrage -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100 pt-0" id="info_softwareRequest">
                          <a href="{{route('softwareRequest')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/software_300.png" id="softwareanfrage" style="max-width: 200px;" alt="" />
                                </div>
                                <div class="text-container">
                                  <h6>Softwareanfrage</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- Hardware-anfrage (computer request) -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_hardwareRequest">
                          <a href="{{route('hardwareRequest')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                  <img src="/images/admin_images/hardware_kelner_400.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                  <h6>Hardware-Anfrage</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- Peripherie-anfrage (peripheral Request) -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_peripheralRequest">
                          <a href="{{route('peripheralRequest')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                  <img src="/images/admin_images/peripherals.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">                                    
                                    <h6>Peripherie-Anfrage</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- Drucker hinzu / ent -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_printer">
                          <a href="{{route('printer_in_out')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/Printer_Walk_300.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>Drucker hinzu./ent.</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <!-- Sonstiges -->
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100 mb-5" id="info_other"> <!-- ? margin button on the last element-->
                          <a href="{{route('other')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/Question_300.png"  style="max-width:200px;" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>Sonstiges...</h6>
                                </div>
                              </div>
                            </div>
                          </a>
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

  $('#info_softwareRequest').hover(function(){
      underform.children().remove();
      underform.append(
        `
          <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Softwaranfrage</h2>
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

  $('#info_peripheralRequest').hover(function(){
      underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Peripherie-Anfrage</h2>
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

  $('#info_hardwareRequest').hover(function(){
    underform.children().remove();
      underform.append(
        `
        <div class="card-flyer2">
            <div class="text-box2">
              <div class="text-container2">
                <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                <h2>Hardware-anfrage</h2>
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

  $('#info_problems').hover(function(){
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

  $('#info_printer').hover(function(){
  underform.children().remove();
    underform.append(
      `
      <div class="card-flyer2">
          <div class="text-box2">
            <div class="text-container2">
              <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
              <h2>Drucker Treiber</h2>
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
  $('#info_other').hover(function(){
  underform.children().remove();
    underform.append(
      `
      <div class="card-flyer2">
          <div class="text-box2">
            <div class="text-container2">
              <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
              <h2>Sonstiges...</h2>
                <ul class="list-unstyled">
                  <li>Alle Anfragen, welche Sie nicht den restlichen</li>
                  <li> Kategorien zuordnen können.</li>
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