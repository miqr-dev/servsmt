@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Drucker'])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <div class="row mx-auto justify-content-center">
              <div class="col-lg-12">
                <div class="card card-primary card-outline" id="underform">
                  <!-- Content here -->
                  <div id="cards_landscape_wrap-2">
                    <div class="container">
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_printer_printer">
                          <a href="{{route('printer_in_out')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                  <img src="/images/admin_images/Printer_Walk_300.png" alt="" />
                                </div>
                                <div class="text-container">
                                  <h6>Drucker einrichten</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info-scanner">
                          <a href="{{route('scanner')}}">
                            <div class="card-flyer">
                              <div class="text-box">
                                <div class="image-box">
                                    <img src="/images/admin_images/scanner_300.png" alt="" />
                                </div>
                                <div class="text-container">
                                    <h6>Scanner Probleme</h6>
                                </div>
                              </div>
                            </div>
                          </a>
                          </div>
                          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_functuality">
                            <a href="{{route('functuality')}}">
                              <div class="card-flyer">
                                <div class="text-box">
                                  <div class="image-box">
                                      <img src="/images/admin_images/function_300.png" alt="" />
                                  </div>
                                  <div class="text-container">
                                      <h6>Funktionsanfrage</h6>
                                  </div>
                                </div>
                              </div>
                            </a>
                          </div>
                          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100 mb-5" id="info_error"> <!-- ? margin button on the last element-->
                            <a href="{{route('errors')}}">
                              <div class="card-flyer">
                                <div class="text-box">
                                  <div class="image-box">
                                      <img src="/images/admin_images/x_300.png" alt="" />
                                  </div>
                                  <div class="text-container">
                                      <h6>Probleme</h6>
                                  </div>
                                </div>
                              </div>
                            </a>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100"></div>
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 h-100 align-self-center" id="tooltip_box_printer" >
                          <!-- ! Jquery forms here -->
                          <!-- ! Jquery forms ends here -->
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div> <!--end second card -->
            </div>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</section>

@endsection
@section('script')
<script>
  $(document).ready(function() {
let underform = $('div #tooltip_box_printer');
let rm_children = underform.children().remove();
rm_children;

$('#info_printer_printer').hover(function(){
  underform.children().remove();
    underform.append(
    `
    <div class="card-flyer2">
        <div class="text-box2">
          <div class="text-container2">
            <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
            <h2>Drucker Einrichten</h2>
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

  $('#info-scanner').hover(function(){
  underform.children().remove();
    underform.append(
    `
    <div class="card-flyer2">
        <div class="text-box2">
          <div class="text-container2">
            <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
            <h2>Scanner Probleme</h2>
              <ul class="list-unstyled">
                <li>Scanner einrichten oder Probleme mit dem Scanner melden</li>
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

  $('#info_functuality').hover(function(){
  underform.children().remove();
    underform.append(
    `
    <div class="card-flyer2">
        <div class="text-box2">
          <div class="text-container2">
            <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
            <h2>Funktionsanfrage</h2>
              <ul class="list-unstyled">
                <li>Weiß Keine...</li>
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

  $('#info_error').hover(function(){
  underform.children().remove();
    underform.append(
    `
    <div class="card-flyer2">
        <div class="text-box2">
          <div class="text-container2">
            <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
            <h2>Probleme</h2>
              <ul class="list-unstyled">
                <li>Probleme bei Druck, Kopie etc.</li>
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
