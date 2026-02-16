@extends('layouts.admin_layout.admin_layout')

<style>
  /* chat profile photo */
  .rounded-circle {
    border-radius: 50%;
    width: 50px;
    height: 50px;
  }

  /* ticket underline */
  u {
    padding-bottom: 3px !important;
    text-decoration: none !important;
    border-bottom: 2px solid #661421 !important;
  }

  .admin_notes {
    height: 200px !important;
  }

  #wrapNext+div {
    white-space: break-spaces !important;
  }
</style>

@section('content')
@include('tickets.layout_ticket.header',['title'=>''])

<!-- Main content -->
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-secondary card-outline">
          <div class="card-body box-profile form-group">
            <div class="row mx-auto">
              <!-- first card -->
              <div class="col-lg-4">
                <div class="card card-secondary card-outline">
                  <div class="card-body box-profile form-group">
                    <div class="row">
                      <div class="form-group col-md-12">
                        @if($handwerk->deleted_at)
                        <p class="text-center lead">Erledigt von </p>
                        <h3 class="text-center" style="color: #661421;"><b>{{$handwerk->done_by}}</b></h3>
                        @else
                        <p class="text-center lead">Zugewiesen an </p>
                        <h3 class="text-center" style="color: #661421;"><b>{{$handwerk->user['username']}}</b></h3>
                        @endif
                      </div>
                      @if($handwerk->deleted_at)
                      <div class="form-group col-md-12">
                        <form action="{{ route ('handwerk.restore',$handwerk->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-primary col-md-12">Wiederherstellen</button>
                        </form>
                      </div>
                      @else
                      <!-- @if(auth()->user()->hasAnyRole(['Super_Admin','handwerk_admin']))
                      <div class="form-group col-md-12 mb-1">
                        <form action="{{ route ('handwerk.delete',$handwerk->id) }}" method="POST">
                          @csrf
                          @if(request('from_city'))
                          <input type="hidden" name="from_city" value="{{ request('from_city') }}">
                          @endif
                          <button type="submit" class="btn btn-success col-md-12" value="Delete">Erledigt</button>
                        </form>
                      </div>
                      <div class="form-group col-md-12">
                        <select id="assignedTo" name="assignedTo"
                          class="assignTo btn col-md-12 bg-dark text-white text-center">
                          <option value="">Zuweisen</option>
                          @foreach($admins as $admin)
                          <option value="{{ $admin->id }}" {{$admin->id == @$handwerk->assignedTo ? 'selected' : ''
                            }}>{{
                            $admin->username }}</option>
                          @endforeach
                          //  Add the specific user "Steven Stefanowsky" only if the standort is "Leipzig"
                          @if($handwerk->submitter_standort == 'Leipzig')
                          <option value="14441" {{ @$handwerk->assignedTo == 14441 ? 'selected' : '' }}>Steven Stefanowsky
                          </option>
                          @endif
                        </select>
                      </div>
                      @endif -->
                      @if(auth()->user()->hasAnyRole(['Super_Admin', 'handwerk_admin', 'Sekretariat']))
                      <div class="form-group col-md-12 mb-1">
                        <!-- Only users with Super_Admin, handwerk_admin, or Sekretariat roles can see this -->
                        <form action="{{ route('handwerk.delete', $handwerk->id) }}" method="POST">
                          @csrf
                          @if(request('from_city'))
                          <input type="hidden" name="from_city" value="{{ request('from_city') }}">
                          @endif
                          <button type="submit" class="btn btn-success col-md-12" value="Delete">Erledigt</button>
                        </form>
                      </div>
                      @endif

                      @if(auth()->user()->hasAnyRole(['Super_Admin', 'handwerk_admin']))
                      <div class="form-group col-md-12">
                        <!-- Only users with Super_Admin or handwerk_admin roles can see this -->
                        <select id="assignedTo" name="assignedTo"
                          class="assignTo btn col-md-12 bg-dark text-white text-center">
                          <option value="">Zuweisen</option>
                          @foreach($admins as $admin)
                          <option value="{{ $admin->id }}" {{ $admin->id == @$handwerk->assignedTo ? 'selected' : '' }}>
                            {{ $admin->username }}
                          </option>
                          @endforeach
                          <!-- Add the specific user "Steven Stefanowsky" only if the standort is "Leipzig" -->
                          @if($handwerk->submitter_standort == 'Leipzig')
                          <option value="14441" {{ @$handwerk->assignedTo == 14441 ? 'selected' : '' }}>
                            Steven Stefanowsky
                          </option>
                          @endif
                        </select>
                      </div>
                      @endif

                      @endif

                      <div class="form-group col-md-6">
                        <label for="submitter"> Ersteller</label>
                        <input type="text" class="form-control" name="submitter" value="{{$handwerk->submitter_name}}"
                          readonly>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="ticket_created_at"> Am</label>
                        <input type="text" class="form-control" name="ticket_created_at"
                          value="{{$handwerk->created_at->format('d M Y')}}" readonly>
                      </div>
                    </div>
                    <div class="row">
                      <div class="form-group col-md-6">
                        <label for="submitter_standort"> Standort</label>
                        <input type="text" class="form-control" name="submitter_standort" id="submitter_standort"
                          value="{{@$handwerk->submitter_standort}}" readonly>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="submitter_adresse">Adresse</label>
                        <input type="text" class="form-control" name="submitter_adresse"
                          value="{{@$handwerk->submitter_adresse}}" readonly>
                      </div>
                    </div>
                    <div class="row">
                      <div class="form-group col-md-12">
                        <label for="tel_number"> Telefon</label>
                        <input type="text" class="form-control" name="tel_number" value="{{$handwerk->tel_number}}"
                          readonly>
                      </div>
                    </div>
                  </div> <!-- Closing div for card-body -->
                </div> <!-- Closing div for card -->
              </div> <!-- Closing div for column of first card -->

              <!--end first card -->

              <!-- second card -->
              <div class="col-lg-8">
                <div class="card card-secondary card-outline">
                  <div class="card-body box-profile form-group">
                    <div class="row">
                      <!-- PC & Laptop -->
                      @include($blade_name)
                      <div class="col-md-12 invoice-col mt-2">
                        <strong style="color:#661421;">Beschreibung <i class="far fa-comment-dots fa-lg"></i></strong>
                        <div class="col-md-12 mt-2 wrapNext" id="wrapNext">
                          {!!@$handwerk->notizen!!}
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-12">
                        <div class="form-group col-md-6 col-lg-12 ml-3">
                          <label for="beschreibung"> Kommentar </label>
                        </div>
                        <div class="col-md-12 ml-3">
                          @comments(['model' => $handwerk])
                        </div>
                      </div>
                    </div>
                  </div> <!-- Closing div for card-body -->
                </div> <!-- Closing div for card -->
              </div> <!-- Closing div for column of second card -->
              <!--end second card -->
            </div> <!-- Closing div for row -->
          </div> <!-- Closing div for card-body -->
        </div> <!-- Closing div for card -->
      </div> <!-- Closing div for col-12 mx-auto -->
    </div> <!-- Closing div for row -->
  </div> <!-- Closing div for container-fluid col-lg-12 -->
</section>

@endsection

@section('script')
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var timer = null;
  $('#admin_notes_handwerk').keydown(function () {
    clearTimeout(timer);
    timer = setTimeout(saveNote, 1000)
  });
  function saveNote() {
    let $adminNotes = $('#admin_notes_handwerk').val();
    $.ajax({
      type: "POST",
      url: "{{route('handwerk.admin_notes')}}",
      data: { "adminNotes": $adminNotes, "ticket_id": {{ $handwerk-> id }} 
      },
  success: function (result) {
  }
    });
  }

  $("#assignedTo").change(function () {
    var assignedTo = $(this).val();
    $.ajax({
      url: "{{route('handwerk.assignedTo')}}",
      method: "POST",
      data: {
        assignedTo: assignedTo,
        handwerkId: {{ $handwerk-> id }}  
    },
    success: function (result) {

    },
  });
});


</script>
@endsection