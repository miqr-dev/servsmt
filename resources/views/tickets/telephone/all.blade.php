@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Ticketanfrage'])

  <section class="content">
    <div class="container-fluid col-lg-12">
      <div class="row">
        <div class="col-12 mx-auto">
          <!-- Profile Image -->
          <div class="card card-primary card-outline">
            <div class="card-body box-profile form-group">

                <!-- child cards -->
                <div class="row mx-auto justify-content-center">
                  <!-- second card -->
                  <div class="col-lg-12">
                    <div class="card card-primary card-outline" id="underform">
                      <!-- Content here -->
                      <div id="cards_landscape_wrap-2">
                        <div class="container">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100" id="info_notworking_telephone">
                                    <a href="{{route ('tel_problems')}}">
                                        <div class="card-flyer">
                                            <div class="text-box">
                                                <div class="image-box">
                                                    <img src="/images/admin_images/tel_not_working_400.png" alt="" />
                                                </div>
                                                <div class="text-container">
                                                  <h6>Probleme</h6>
                                              </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 h-100 mb-5" id="info_change_number_telephone"><!-- ? margin button on the last element-->
                                  <a href="{{route ('tel_changes')}}">
                                    <div class="card-flyer">
                                      <div class="text-box">
                                        <div class="image-box">
                                          <img src="/images/admin_images/mix_move.png" alt="" />
                                        </div>
                                        <div class="text-container">                                    
                                            <h6>Änderungen</h6>
                                        </div>
                                      </div>
                                    </div>
                                  </a>
                              </div>
                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 h-100 align-self-center" id="tooltip_box_telephone" >
                              <!-- ! Jquery forms here --> 
                              <!-- ! Jquery forms ends here -->
                            </div>

                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  <!--end second card -->
              </div>
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

@endsection

@section('script')
<script>
  $(document).ready(function() {
  let underform = $('div #tooltip_box_telephone');
  let rm_children = underform.children().remove();
  rm_children;
  
    $('#info_notworking_telephone').hover(function(){
        underform.children().remove();
        underform.append(
          `
            <div class="card-flyer2">
              <div class="text-box2">
                <div class="text-container2">
                  <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                  <h2>Probleme</h2>
                    <ul class="list-unstyled">
                      <li>Jegliche Probleme bzgl. des Telefons.</li>
                    </ul>
                </div>
              </div>
            </div>
          `
        );
  
      },function(){
        underform.children().remove(); 
      })
  
    $('#info_change_number_telephone').hover(function(){
        underform.children().remove();
        underform.append(
          `
          <div class="card-flyer2">
              <div class="text-box2">
                <div class="text-container2">
                  <img src="/images/admin_images/info_400_2.png" style="max-width:100px;  alt="" />
                  <h2>Änderungen</h2>
                    <ul class="list-unstyled">
                      <li>Jegliche Änderungswünsche bzgl. dem Telefons.</li>
                    </ul>
                </div>
              </div>
            </div>
          `
        );
  
      },function(){
        underform.children().remove(); 
      })
  
    $('#info_telephone_change').hover(function(){
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

    })
  
  </script>
@endsection