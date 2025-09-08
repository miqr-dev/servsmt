@extends('layouts.admin_layout.admin_layout')
<style>
  .row {
	 margin: 0 0 !important;
}
 .course_card {
	 margin: 25px 10px;
	 position: relative;
	 display: flex;
	 flex-direction: column;
	 min-width: 0;
	 word-wrap: break-word;
	 background-color: #fff;
	 background-clip: border-box;
	 transition: 0.25s ease-in-out;
}
 .course_card_img {
	 max-height: 100%;
	 max-width: 100%;
}
 .course_card_img img {
	 height: 250px;
	 width: 100%;
	 transition: 0.25s all;
}
 .course_card_img img:hover {
	 transform: translateY(-3%);
}
 .course_card_content {
	 padding: 16px;
}
 .course_card_content h3 {
	 font-family: nunito sans;
	 font-family: 18px;
}
 .course_card_content p {
	 font-family: nunito sans;
	 text-align: justify;
}
 .course_card_footer {
	 padding: 10px 0px;
	 margin: 16px;
}
 .course_card_footer a {
	 text-decoration: none;
	 font-family: nunito sans;
	 margin: 0 10px 0 0;
	 text-transform: uppercase;
	 color: #f96332;
	 padding: 10px;
	 font-size: 14px;
}
 .course_card:hover {
	 transform: scale(1.025);
	 border-radius: 0.375rem;
	 box-shadow: 0 0 2rem rgba(0, 0, 0, .25);
}
 .course_card:hover .course_card_img img {
	 border-top-left-radius: 0.375rem;
	 border-top-right-radius: 0.375rem;
}
 
</style>
@section('content')
<div class="card card-primary card-outline">
  <div class="card-body">
  <h5 class="card-title" style="color:#661421">Ticket Videos</h5>
    <div class="row mx-auto">
      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="course_card">
          <div class="course_card_img">
            <video
            fluid="true"
            id="my-video"
            class="video-js"
            controls
            preload="auto"
            width="400"
            height="200"
            data-setup="{}"
            poster="/images/admin_images/smt_background.png"
            >
            <source src="/images/admin_images/smt3.mp4" type="video/mp4" />
          </video>
          </div>
          <div class="course_card_content">
            <h3 class="title">Ticket System</h3>
            <p class="description">Übersicht und Info zum Ticketsystem. </p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="course_card">
          <div class="course_card_img">
            <video
            fluid="true"
            id="my-video"
            class="video-js"
            controls
            preload="auto"
            width="400"
            height="200"
            data-setup="{}"
            poster="/images/admin_images/Ticket_erstellen_background.png"
            >
            <source src="/images/admin_images/Ticket_Erstellen.mp4" type="video/mp4" />
          </video>
          </div>
          <div class="course_card_content">
            <h3 class="title">Benutzeranfrage</h3>
            <p class="description">Anleitung zur neuen Benutzeranfrage über das Ticket-System.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="course_card">
          <div class="course_card_img">
            <video
            fluid="true"
            id="my-video"
            class="video-js"
            controls
            preload="auto"
            width="400"
            height="200"
            data-setup="{}"
            poster="/images/admin_images/inbox_background.png"
            >
            <source src="/images/admin_images/inbox3.mp4" type="video/mp4" />
          </video>
          </div>
          <div class="course_card_content">
            <h3 class="title">"Meine" Tickets</h3>
            <p class="description">Übersicht über die Ticketerstellung und bereits erledigte Tickets.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="course_card">
          <div class="course_card_img">
            <video
            fluid="true"
            id="my-video"
            class="video-js"
            controls
            preload="auto"
            width="400"
            height="200"
            data-setup="{}"
            poster="/images/admin_images/teilnehmerList_background.png"
            >
            <source src="/images/admin_images/Teilnehmer_liste.mp4" type="video/mp4" />
          </video>
          </div>
          <div class="course_card_content">
            <h3 class="title">Teilnehmerliste</h3>
            <p class="description">Kurze Übersicht der Teilnehmerliste und Ihrer Funktionsweise.</p>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<div class="d-flex flex-row">
  <div class="d-flex justify-content-around col-lg-12">
    <div class="card card-primary card-outline col-lg-6 mx-2">
      <div class="card-body">
      <h5 class="card-title" style="color:#661421">Profil Video</h5>
        <div class="row mx-auto">
          <div class="col-lg-6 col-md-12 col-sm-12">
            <div class="course_card">
              <div class="course_card_img">
                <video
                fluid="true"
                id="my-video"
                class="video-js"
                controls
                preload="auto"
                width="400"
                height="200"
                data-setup="{}"
                poster="/images/admin_images/profile.png"
                >
                <source src="/images/admin_images/profile.mp4" type="video/mp4" />
              </video>
              </div>
              <div class="course_card_content">
                <h3 class="title">Benutzerprofil</h3>
                <p class="description">Infos und Änderung der Outlook Signatur.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card card-primary card-outline col-lg-6">
      <div class="card-body">
      <h5 class="card-title" style="color:#661421">Inventar</h5>
        <div class="row mx-auto">
          <div class="col-lg-6 col-md-12 col-sm-12">
            <div class="course_card">
              <div class="course_card_img">
                <video
                fluid="true"
                id="my-video"
                class="video-js"
                controls
                preload="auto"
                width="400"
                height="200"
                data-setup="{}"
                poster="/images/admin_images/bewegung_background.png"
                >
                <source src="/images/admin_images/inventar_bewegen.mp4" type="video/mp4" />
              </video>
              </div>
              <div class="course_card_content">
                <h3 class="title">Inventar</h3>
                <p class="description">PC Standort ändern </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> 
</div>
</div>  
@endsection
@section('script')

@endsection