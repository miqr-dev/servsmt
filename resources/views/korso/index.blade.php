@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header', [
'title' => 'Korso Aufgaben',
'colorClass' => 'ticket_header_korso',
'buttonClass' => 'btn-outline-green' // New class for button color
])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
        <div class="card card-thirdary card-outline" id="content">
          <div class="card-body box-profile form-group">
            <div class="row mx-auto justify-content-center">
              <div class="col-lg-12">
                <div class="row d-flex justify-content-around">
                </div>
              </div>
            </div>
          </div>
          <div class="container-fluid">
            <div class="row justify-content-center">


              <div class="col-md-4 d-flex justify-content-center">
                <div class="card card-thirdary card-outline mb-4 w-100">
                  <div class="card-header text-center font-weight-bold" style="color:#65A30D;">
                    <a href="{{ route('zertifizierung') }}" class="text-decoration-none">

                      <h6 class="text-center" style="color: #65A30D;">Zertifizierung & Qualitätsmanagement</h6>
                    </a>
                  </div>

                  <!-- Info Section -->
                  <div class="card-body">
                    <p class="text-muted text-center">
                      <i class="fas fa-info-circle"></i>
                      Falls Sie ein Ticket für <strong>Zertifizierung & Qualitätsmanagement</strong> einreichen möchten,
                      klicken Sie bitte auf den obigen Link. Dies umfasst Anfragen zu
                      <span class="text-dark">Beantragung Maßnahmenummer, Erstellung Kurzkonzepte,</span>
                      <span class="text-dark">IS+ Verkaufsartikeln/Kurse,</span>
                      <span class="text-dark">BAMF, Ausschreibungen</span> sowie
                      <span class="text-dark">Fehlermeldungen oder Aktualisierungen in Kursnet, InfoNet &
                        Laufwerken</span>.
                    </p>
                  </div>
                </div>
              </div>


              <div class="col-md-4 d-flex justify-content-center">
                <div class="card card-thirdary card-outline mb-4 w-100">
                  <div class="card-header text-center font-weight-bold" style="color:#65A30D;">
                    <a href="{{route('printmarketing')}}" class="text-decoration-none">
                      <h6 class="text-center" style="color: #65A30D;">Printmarketing</h6>
                    </a>
                  </div>

                  <!-- Info Section -->
                  <div class="card-body">
                    <p class="text-muted text-center">
                      <i class="fas fa-info-circle"></i>
                      Falls Sie ein Ticket für <strong>Printmarketing</strong> einreichen möchten,
                      klicken Sie bitte auf den obigen Link. Dies umfasst Anfragen zu
                      <span class="text-dark">Flyern, Visitenkarten, Give-Aways, Messen</span>,
                      <span class="text-dark">Beklebungen & Beschilderungen</span> sowie
                      <span class="text-dark">Flucht- und Rettungsplänen</span> oder sonstigen Anliegen.
                    </p>
                  </div>
                </div>
              </div>


              <div class="col-md-4 d-flex justify-content-center">
                <div class="card card-thirdary card-outline mb-4 w-100">
                  <div class="card-header text-center font-weight-bold" style="color:#65A30D;">
                    <a href="{{route('onlinemarketing')}}" class="text-decoration-none">
                      <h6 class="text-center" style="color: #65A30D;">Onlinemarketing</h6>
                    </a>
                  </div>

                  <!-- Info Section (No List, Just Text) -->
                  <div class="card-body">
                    <p class="text-muted text-center">
                      <i class="fas fa-info-circle"></i>
                      Falls Sie ein Ticket für <strong>Onlinemarketing</strong> einreichen möchten,
                      klicken Sie bitte auf den obigen Link. Dies umfasst Anfragen zu
                      <span class="text-dark">Fehlermeldungen oder Aktualisierungen auf der Website</span>,
                      <span class="text-dark">Social Media Beiträge oder Ideen</span> sowie
                      <span class="text-dark">den Versand von Newslettern & Anschreiben</span>.
                    </p>
                  </div>
                </div>
              </div>


            </div>
          </div>
        </div>
      </div>
    </div><!-- end row -->
  </div>
</section>
@endsection

@section('script')

<script>

</script>
@endsection