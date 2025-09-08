@extends('layouts.admin_layout.admin_layout')
<style>
    .img-size {
        width: 180px !important;
        height: 180px !important;
    }

    .poll-name {
  text-overflow: ellipsis;
  word-wrap: break-word;
  display: block;
  line-height: 1em; /* a */
  max-height: 2em; /* a x number of line to show (ex : 2 line)  */
}
    

</style>

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <fieldset class="border rounded px-2 mb-2">
      <legend class="col-lg-6">Nach Standort Suchen</legend>
        <div class="form-row mb-3">
          <div class="form-group col-md-4 non_print">
            <select id="location_id_contacts" name="location_id_contacts" class="form-control" required></select>
          </div>
          <div class="form-group col-md-4 non_print">
            <select id="room_id_contacts" name="room_id_contacts" class="form-control" required></select>
          </div>
        </div>
        </fieldset>
        <fieldset class="border rounded px-2 mb-2">
            <legend class="w-auto">Suche nach Name</legend>
            <ul class="nav nav-pills nav-justified mb-3">
              <li class="nav-item col-md-4">
                <select class="custom-select js-example-basic-single">
                  <option class="form-control" selected disabled value="-1"></option>
                  @foreach($users as $user)
                  <option class="form-control" name="searchByName" value="{{ $user['id'] }}">{{ $user["name"] }}
                  </option>
                  @endforeach
                </select>
              </li>
            </ul>
        </fieldset>
        <!-- Default box -->
        <div class="card card-solid">
          <div class="card-body pb-0">
            <div class="row" id="UnderCard">
              <!-- JQuery goes here -->
            </div>
          </div> <!-- /.card-body -->
          <div class="card-footer"></div>
        </div><!-- /.card -->
    </div>
</section>
<!-- /.content -->

@endsection @section('script')
<script>
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
    }
});
  $(document).ready(function() {
    $("div#UnderCard").children().remove();
    let selectAddresscontacts = new Array();
    let roomcontacts = new Array();
    $("#location_id_contacts").find("option").remove();
    $("#location_id_contacts").find("optgroup").remove();
    $("#location_id_contacts").append(new Option("Standort...", ""));
    $("#rooms_id_contacts").find("option").remove();
    $("#rooms_id_contacts").append(new Option("Raum...", ""));
    $.ajax({
      type: "get",
      url: "{{route('item.listen')}}"
    }).done(function(data) {
      selectAddresscontacts = new Array();
      $.each(data["places"], function(index, item) {
          $("body #location_id_contacts").append(
              '<optgroup label="' +index +'" id="' +item +'" ></optgroup>'
          );
      });
      $.each(data["locations"], function(index, item) {
          $("#location_id_contacts #" + item.place_id).append(
              new Option(item.address, item.id)
          );
          selectAddresscontacts.push(item);
      });
    });

  $(document).on("change", "#location_id_contacts", function() {
  $("div#UnderCard").children().remove();
    $.ajax({
    type: "post",
    url: "{{ route('contacts_phones_in_location') }}",
    data: {location_id: $("#location_id_contacts").val()},
    success: function(resp) {
    
    $("div#UnderCard").append(`
      <div class="container">
        <h1 class="text-center" style="color:#661421;">
          ${resp.locationAddress.place.pnname}
        </h1>
        <h2 class="text-center" style="color:#661421;">
          ${resp.locationAddress.address}
        </h2>
      </div>
      <div class="container-fluid mt-3">
        <div class="card card-primary card-outline text-center">
          <div class="card-body">
            <div class="d-flex justify-content-around">
              <div style="color: #661421;">
                  <h5 class="font-weight-bold" data-toggle="tooltip" data-placement="top" title="Telefon"><i class="fas fa-phone fa-xl"></i>
                    ${resp.locationAddress.telephone}
                  </h5>
                </div>
                    <div style="color: #661421;">
                        <h5 class="font-weight-bold" data-toggle="tooltip" data-placement="top" title="FAX">
                            <i class="fas fa-scroll fa-xl"></i>
                            ${resp.locationAddress.fax}
                        </h5>
                    </div>
                    <div style="color: #661421;">
                        <h5 class="font-weight-bold">
                            <i class="fas fa-at fa-xl"></i>
                            <a href="mailto:${resp.locationAddress.email}" target="_blank" rel="noopener noreferrer">info@miqr.de</a>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table class="table" id="contact_table" style="display: none;">
      <thead>
          <tr>
              <th scope="col">Name</th>
              <th scope="col">Abteilung</th>
              <th scope="col">Telefon Nummber</th>
              <th scope="col">Raum</th>
              <th scope="col">Email</th>
          </tr>
      </thead>
      <tbody></tbody>
  </table>`
  );
    $("body #contact_table").show();
    $("body #contact_table tbody").empty();
    for (const item of resp.locationContacts) {
      let gtyp = item.gtyp ? item.gtyp : "-"
      $("body #contact_table tbody").append(
      '<tr id="' + item.invnr +'"><td id="' + item.invnr + 'tel" style="font-weight:bold;">' +
          item.tel_users.map(i => i.name +', ' + i.vorname).join(' & ')
          +
          "</td><td>" +
          item.tel_users.map(i => i.abteilung).join(' ... ') +
          "</td><td>" +
          item.gname +
          "</td><td>" +
          item.invroom.rname +
          "&nbsp;" +
          "||" +
          "&nbsp;" +
          item.invroom.altrname +
          "</td><td>" +
         item.tel_users.map(i => i.email).join(' || ') +
        "</td></tr>"
      );
    };
  },
    error: function() {
        alert("Error");
    }
  });
  $("#room_id_contacts").find("option").remove();
  $("#room_id_contacts").append(new Option("Raum...", ""));
  for (let i = 0; i < selectAddresscontacts.length; i++) {
    if (selectAddresscontacts[i].id == $(this).val()) {
      $.each(selectAddresscontacts[i].invrooms, function(index,item) {
        $("body #room_id_contacts").append(new Option(item.rname + " (" + item.altrname + ")",item.id)
        );
        roomcontacts.push(item);
      });
    }
  }
  });


  $(document).on("change", "#room_id_contacts", function() {
    $.ajax({
      type: "post",
      url: "{{ route('contacts_phones_in_room') }}",
      data: {
          room_id: $("#room_id_contacts").val()
      },
      success: function(resp) {
      $("body #contact_table").show();
      $("body #contact_table tbody").empty();
        for (const item of resp) {
          let gtyp = item.gtyp ? item.gtyp : "-";
          $("body #contact_table tbody").append(
          '<tr id="' +
            item.invnr +
            '"><td id="' +
            item.invnr +
            'tel" style="font-weight:bold;">' +
            item.tel_users.map(i => i.name +', ' + i.vorname).join(' & ')
            +
            "</td><td>" +
            item.tel_users.map(i => i.abteilung).join(' ... ') +
            "</td><td>" +
            item.gname +
            "</td><td>" +
            item.invroom.rname +
            "&nbsp;" +
            "||" +
            "&nbsp;" +
            item.invroom.altrname +
            "</td><td>" +
            item.tel_users.map(i => i.email).join(' || ') +
            "</td></tr>"
          );
        };
      },
      error: function() {
          alert("Error");
      }
    });

    // $("#room_id_contacts").find("option").remove();
    // $("#room_id_contacts").append(new Option("Raum...", ""));
    for (let i = 0; i < selectAddresscontacts.length; i++) {
      if (selectAddresscontacts[i].id == $(this).val()) {
        $.each(selectAddresscontacts[i].invrooms, function(index,item) {
          $("body #room_id_contacts").append(
            new Option(item.rname + " (" + item.altrname + ")",item.id)
          );
          roomcontacts.push(item);
        });
      }
    }
  });
 
  function delay(callback, ms) {
    var timer = 0;
    return function() {
      var context = this,
          args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function() {
          callback.apply(context, args);
      }, ms || 0);
    };
  }
  // In your Javascript (external .js resource or <script> tag)
  $(".js-example-basic-single").select2({
      placeholder: {
          id: "-1", // the value of the option
          text: "Bitte Wählen..."
      },
      allowClear: true
  });

  $(".js-example-basic-single").on("change", function() {
    $("div#UnderCard").children().remove();
    var name = $(".js-example-basic-single option:selected").text();
    $.ajax({
      type: "POST",
      url: "{{route('searchByName')}}",
      data: {searchbyname: name},
      success: function(resp) {
        $.each(resp, function(index, item) {
        $("div#UnderCard").append(
        `<div class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch">
          <div class="card bg-light col-md-12">
            <div class="card-header text-center text-muted border-bottom-0 bg-light">
              <h1 style="color:#661421; font-size:larger; font-weight:bold; ">${item.position}</h1> 
            </div>
            <div class="card-body pt-0">
            <div class="row">
              <div class="col-6">
                <h3 style="color:#661431; font-weight:bold; white-space: nowrap;">${item.name},&nbsp${item.vorname}</h3>
                <br>
                <p class="text-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#15803d" fill-rule="evenodd" d="M3.05492878,13 L1,13 L1,11 L3.05492878,11 C3.5160776,6.82838339 6.82838339,3.5160776 11,3.05492878 L11,1 L13,1 L13,3.05492878 C17.1716166,3.5160776 20.4839224,6.82838339 20.9450712,11 L23,11 L23,13 L20.9450712,13 C20.4839224,17.1716166 17.1716166,20.4839224 13,20.9450712 L13,23 L11,23 L11,20.9450712 C6.82838339,20.4839224 3.5160776,17.1716166 3.05492878,13 Z M12,5 C8.13400675,5 5,8.13400675 5,12 C5,15.8659932 8.13400675,19 12,19 C15.8659932,19 19,15.8659932 19,12 C19,8.13400675 15.8659932,5 12,5 Z M12,8 C14.209139,8 16,9.790861 16,12 C16,14.209139 14.209139,16 12,16 C9.790861,16 8,14.209139 8,12 C8,9.790861 9.790861,8 12,8 Z M12,10 C10.8954305,10 10,10.8954305 10,12 C10,13.1045695 10.8954305,14 12,14 C13.1045695,14 14,13.1045695 14,12 C14,10.8954305 13.1045695,10 12,10 Z"/><title>Standort</title>
                  </svg>
                    <span class="ml-3" style="color:#661421; font-weight:bold;">${item.ort} ,${item.plz}</span></p>
                <p class="text-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" data-name="Layer 1" viewBox="0 0 24 24"><path fill="#0369a1" d="M14,8h1a1,1,0,0,0,0-2H14a1,1,0,0,0,0,2Zm0,4h1a1,1,0,0,0,0-2H14a1,1,0,0,0,0,2ZM9,8h1a1,1,0,0,0,0-2H9A1,1,0,0,0,9,8Zm0,4h1a1,1,0,0,0,0-2H9a1,1,0,0,0,0,2Zm12,8H20V3a1,1,0,0,0-1-1H5A1,1,0,0,0,4,3V20H3a1,1,0,0,0,0,2H21a1,1,0,0,0,0-2Zm-8,0H11V16h2Zm5,0H15V15a1,1,0,0,0-1-1H10a1,1,0,0,0-1,1v5H6V4H18Z"/><title>Straße</title></svg>
                    <span class="ml-3" style="color:#661421; font-weight:bold;">${item.straße}</span></p>
                <p class="text-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#be123c" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/><title>Rufnummer</title></svg>
                    <span class="ml-3" style="color:#661421; font-weight:bold;">${item.tel}</span></p>
                <p class="text-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 64 64" fill="#9f1239" stroke-width="1px" stroke="#000"><path d="M53.26 54.62a4.09 4.09 0 0 0 2.51-5.22l-1.36-3.87A4.1 4.1 0 0 0 49.18 43c-7.73 2.71-10.45-5-11.81-8.88S33.3 22.55 41 19.83a4.1 4.1 0 0 0 2.51-5.22l-1.36-3.87A4.1 4.1 0 0 0 37 8.23c-9.66 3.4-14.1 9.3-7.31 28.63S43.6 58 53.26 54.62z"/><path d="M34.81 48.18A4 4 0 0 0 32 52a4 4 0 0 1-8 0 4 4 0 0 0-8 0 4 4 0 0 1-8 0"/><title>Privatnummer</title></svg>
                    <span class="ml-3" style="color:#661421; font-weight:bold;">${item.privat ? item.privat : 'keine Privatnummer'}</span></p>
                <p class="text-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#881337" fill-rule="evenodd" d="M7,2 L17,2 C18.1045695,2 19,2.8954305 19,4 L19,20 C19,21.1045695 18.1045695,22 17,22 L7,22 C5.8954305,22 5,21.1045695 5,20 L5,4 C5,2.8954305 5.8954305,2 7,2 Z M7,4 L7,20 L17,20 L17,4 L7,4 Z M12,19 C11.4477153,19 11,18.5522847 11,18 C11,17.4477153 11.4477153,17 12,17 C12.5522847,17 13,17.4477153 13,18 C13,18.5522847 12.5522847,19 12,19 Z"/><title>Handynummer</title>
                  </svg>                    
                    <span class="ml-3" style="color:#661421; font-weight:bold;">${item.Mobil ? item.Mobil : 'Keine Handynummer'}</span></p>
                <p class="text-sm poll-name">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#92400e" d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/><title>BusinessUnit</title></svg>                 
                    <span class="ml-3" style="color:#661421; font-weight:bold;">${item.beschreibung ? item.beschreibung : 'Nicht eingegeben'}</span></p>
              </div>
              <div class="col-6 text-center">
                <img src='images/admin_images/mitarbeiter/${item.name}, ${item.vorname}.jpg' alt="" class="img-circle img-fluid img-size mt-5"
                onerror="this.onerror=null;this.src='images/admin_images/mitarbeiter/nopic.jpg';">
                <br>
                <p class="text-sm mt-4">                  
                    <span class="ml-3" style="color:#661421; font-weight:bold; white-space:nowrap;">${item.username}</span></p>
              </div>
            </div>
          </div>
        </div>
      </div>`
      );
     });
    }
   });
  });
  });
</script>
@endsection
