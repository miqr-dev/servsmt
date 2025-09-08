@extends('layouts.admin_layout.admin_layout')
<style>
    #content {
        position: relative;
    }

    .ribbonLeft {
        position: absolute;
        top: -70px;
        left: 45px;
    }

    .ribbonRight {
        position: absolute;
        top: -70px;
        right: 45px;
    }

    .ribbonCenter {
        position: absolute;
        top: -120px;
        right: 900px;
        transform: rotate(-10deg) ;
    }
    .ribbonCenterRight {
        position: absolute;
        top: -100px;
        right: 500px;
        transform: rotate(-10deg) ;
    }

    #news-bar {
      width: 100%;
      box-shadow: 0 4px 10px #661421, 0 0 10px #661421 inset;
      font: 21px bold;
      padding: 8px;
      padding-left: 20px;
      padding-right: 20px;
    }

    #news-bar:hover {
      transition: 0.37s;
    }

    #news-bar a {
      color: #661421;
      text-decoration: none;
    }

    #news-bar a:hover {
      transition: 0.37s;
      color: #661421;
      text-shadow: 0 0 1px white, 0 0 2px white, 0 0 3px white, 0 0 4px white;
    }


   
</style>

@section('content')

  <div id="shownews-bar"> 
  </div>
@include('tickets.layout_ticket.header',['title'=>'Ticketanfrage'])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
       <!-- Profile Image -->
        <div class="card card-primary card-outline" id="content">
          <!-- <img src="/images/admin_images/christmas.png" class="ribbonLeft" style="max-width: 20%; max-height: 20%;"   alt=""> -->
          <!-- <img src="/images/admin_images/santa.png" class="ribbonCenter img-responsive" style="max-width: 25%; max-height: 25%;"   alt=""> -->
          <!-- <img src="/images/admin_images/flagandball2.png" class="ribbonCenterRight" style="max-width: 20%; max-height: 20%;"   alt="">  -->
          <!-- <img src="/images/admin_images/bells.png" class="ribbonRight" style="max-width: 20%; max-height: 20%;"   alt=""> -->
          <div class="card-body box-profile form-group">
            <div class="row mx-auto justify-content-center">
              <div class="col-lg-12">
                <div class="row d-flex justify-content-around">
                  <a href="#" class="btn btn-outline-primary mb-1" data-toggle="modal" data-target="#inbox">Ticket-Erstellung Video Anschauen</a>
                </div>
              </div>
            </div>
          </div>

        <div class="container-fluid">
          <div class="card-deck">
            <div class="card card-primary card-outline mb-4">
              <div class="card-header text-center font-weight-bold" style="color:#661421;">
               Neu - Anforderungen
              </div>
                 <ul class="list-group list-group-flush">
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color:#059669;"><u>Softwareinstallation</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('softwareInstall')}}"><li class="text-weight-bold">neue Software benötigt</li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #0284c7;"><u>Hardwarebedarf</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('peripheralRequest')}}"><li>Maus | Tastatur | Kopfhörer</li>
                      <li>Webcam | Lautsprecher</li></a>
                      <a href="{{route('hardwareRequest')}}"><li>PC | Laptop | Tablet | Telefon</li>
                      <li>Drucker | Beamer | Scanner</li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #9333ea;"><u>Benutzer</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('users_employee')}}"><li>Neuer Mitarbeiter</li></a>
                      <a href="{{route('users_participant')}}"><li>Neuer Teilnehmer</li></a>
                    </ul>
                  </div>
                </ul>
            </div>
            <div class="card card-primary card-outline mb-4">
              <div class="card-header text-center font-weight-bold" style="color:#661421;">
                Probleme & Fehlermeldungen
              </div>
                 <ul class="list-group list-group-flush">
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color:#059669;"><u>Software</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('softwareError')}}"><li>Funktioniert nicht | Aktivieren</li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #0284c7;"><u>PC</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('pc_problems') }}"><li>Geht nicht an | Blue / Black Screen</li>
                      <li>Webcam | Headset | Lautsprecher</li>
                      <li>Tastatur | Maus </li>
                      <li>Sehr langsam</li>
                      <li>Netzwerkzugriff langsam</li>
                      <li>lautes Lüftergeräusch</li>
                      <li>Keine Netzlaufwerke</li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color:#4338ca;"><u>Drucker</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('errors')}}"><li>Druckt nicht</li>
                      <li>Defekt </li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color:#4338ca;"><u>Scanner</u></h5>
                    <ul class="list-unstyled text-center">
                    <a href="{{route('scanner')}}">
                      <li>Scannt nicht</li>
                      <li>Scans nicht im Scan Ordner</li>
                    </a>
                    </ul>
                  </div>
                </ul>
            </div>
            <div class="w-100 d-none d-sm-block d-md-none"><!-- wrap every 2 on sm--></div>
            <div class="card card-primary card-outline mb-4">
              <div class="card-header text-center font-weight-bold" style="color:#661421;">
                Probleme & Fehlermeldungen
              </div>
                 <ul class="list-group list-group-flush">
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #d97706;"><u>Telefon</u></h5>
                    <ul class="list-unstyled text-center">
                    <a href="{{route('tel_problems')}}">
                      <li>Keine Anrufe möglich</li>
                      <li>Keine Verbindung</li>
                      <li>Defekt</li>
                    </a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #4338ca;"><u>Beamer</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('projector_problems')}}"><li>Kein Signal</li>
                      <li>Flackern | Lampe defekt</li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #9333ea;"><u>Benutzer</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('users_loginProblem')}}"><li>Anmeldeprobleme</li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #dc2626;"><u>Web</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('terminal_tn')}}"><li>Terminal TN</li></a>
                      <a href="{{route('bbb')}}"><li>Big Blue Button</li></a>
                      <a href="{{route('vtiger')}}"><li>Vtiger</li></a>
                      <a href="{{route('firmenvz')}}"><li>FirmenVZ</li></a>
                      <a href="{{route('smt')}}"><li>SMT</li></a>
                    </ul>
                  </div>
                </ul>
            </div>
            <div class="w-100 d-none d-md-block d-lg-none"><!-- wrap every 3 on md--></div>
            <div class="card card-primary card-outline mb-4">
              <div class="card-header text-center font-weight-bold" style="color:#661421;">
               Services & Einrichten
              </div>
                 <ul class="list-group list-group-flush">
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color:#4338ca;"><u>Drucker & Scanner</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('printer_in_out')}}"><li class="text-weight-bold">Druckerinstallation | Einrichten </li></a>
                      <a href="{{route('scannerNew')}}"><li class="text-weight-bold">Scannerinstallation | Einrichten </li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #9333ea;"><u>Benutzer</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('users_namechange')}}"><li>Namensänderung</li></a>
                       <a href="{{route('users_email_forward')}}"><li>Email Weiterleiten</li>
                      <li>Weiterleitung abbrechen</li></a>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #d97706;"><u>Telefon</u></h5>
                    <ul class="list-unstyled text-center">
                      <a href="{{route('tel_changes_location')}}"><li>Standort ändern</li></a>
                      <a href="{{route('tel_changes_name')}}"><li>Name ändern</li></a>
                      <a href="{{route('tel_changes_number')}}"><li>Nummer ändern</li></a>
                    </ul>
                  </div>
                </ul>
            </div>
            <div class="w-100 d-none d-sm-block d-md-none"><!-- wrap every 2 on sm--></div>
            <div class="w-100 d-none d-lg-block d-xl-none"><!-- wrap every 4 on lg--></div>
            <div class="card card-primary card-outline mb-4">
              <div class="card-header text-center font-weight-bold" style="color:#661421;">
               Vor Ort Termin
              </div>
                 <ul class="list-group list-group-flush">
                  <div class="card-body list-group-item">
                    <h5 class="text-center" style="color:#0c4a6e;"><u>Berlin </u></h5>
                    <ul class="list-unstyled text-center">
                      <li class="text-weight-bold">{{@$datum->berlin >= Carbon\Carbon::now() ? @$datum->formatted_berlin : "Kein Termin" }}</li>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #075985;"><u>Berlin II</u></h5>
                    <ul class="list-unstyled text-center">
                      <li>{{@$datum->berlinii >= Carbon\Carbon::now() ? @$datum->formatted_berlinii : "Kein Termin" }}</li>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #0369a1;"><u>Chemnitz</u></h5>
                    <ul class="list-unstyled text-center">
                      <li> {{@$datum->chemnitz >= Carbon\Carbon::now() ? @$datum->formatted_chemnitz : "Kein Termin" }}</li>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #0284c7;"><u>Dresden</u></h5>
                    <ul class="list-unstyled text-center">
                      <li>{{@$datum->dresden >= Carbon\Carbon::now() ? @$datum->formatted_dresden : "Kein Termin" }}</li>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #0ea5e9;"><u>Leipzig</u></h5>
                    <ul class="list-unstyled text-center">
                      <li>{{@$datum->leipzig >= Carbon\Carbon::now() ? @$datum->formatted_leipzig : "Kein Termin" }}</li>
                    </ul>
                  </div>
                  <div class="card-body list-group-item" >
                    <h5 class="text-center" style="color: #38bdf8;"><u>Suhl</u></h5>
                    <ul class="list-unstyled text-center">
                      <li>{{@$datum->suhl >= Carbon\Carbon::now() ? @$datum->formatted_suhl : "Kein Termin" }}</li>
                    </ul>
                  </div>
                </ul>
            </div>

          </div>
        </div>


        </div>
      </div>
    </div><!-- end row -->






  </div><!-- end container-fluid-->

    <!-- Modal -->
    <div class="modal fade" id="overall" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-backdrop="false">
        <div class="modal-dialog" style="max-width: 1200px !important;">
            <div class="modal-content container">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <video fluid="true" id="my-video" class="video-js vjs-theme-city" controls preload="auto"
                        width="600" height="400" data-setup="{}">
                        <source src="/images/admin_images/smt3.mp4" type="video/mp4" />
                    </video>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="inbox" tabindex="-1" aria-labelledby="inboxLabel" aria-hidden="true"
        data-backdrop="false">
        <div class="modal-dialog" style="max-width: 1200px !important;">
            <div class="modal-content container">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <video fluid="true" id="my-video" class="video-js vjs-theme-city" controls preload="auto"
                        width="600" height="400" data-setup="{}">
                        <source src="/images/admin_images/inbox3.mp4" type="video/mp4" />
                    </video>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection @section('script')

<script>
    $(document).ready(function () {
        $.ajax({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            type: "GET",
            url: "{{route('news.popup.check')}}",
            success: function (result) {
                if (result[0].isPublished === "on") {
                    var news_title = result[0].title;
                    var news_body = result[0].body;
                    Swal.fire({
                        title: news_title,
                        html: news_body,
                        width: 600,
                        padding: "3em",
                        color: "#661421",
                        showConfirmButton: false,
                        backdrop: `
            rgba(102,20,33,0.5)
            url("/images/admin_images/helpdesk.gif")
            top 150px
            left 900px 
            no-repeat
          `
                    });
                } else {
                    //
                }
            }
        });

    let shownews = $('div#shownews-bar');
    $.ajax({
       headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
       type: "GET",
       url:"{{route('newsbar.check')}}",
       success: function(result) {
        if(result[0].isNewsBar === "on"){
          console.log(result);
           shownews.append(
          `<div id="news-bar" class="mb-3 mt-0"">
            <marquee direction="left" scrollamount="3" behavior="scroll" onmouseover="this.stop()" onmouseout="this.start()">
             <a href="#">${result[0].name}</a>
            </marquee>
           </div>`);
        }
       }
    })
        
    });



</script>

<script>
    $("body").on("hidden.bs.modal", ".modal", function () {
        $("video").trigger("pause");
    });

</script>

@endsection
