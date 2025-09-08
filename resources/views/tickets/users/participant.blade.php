@extends('layouts.admin_layout.admin_layout')
<style>
  .custom-file {
    overflow: hidden;
  }

  .custom-file-control {
    white-space: nowrap;
  }

  .swal2-popup {
    font-size: 1.6rem !important;
  }

  .swal2-title {
    font-size: 2.5rem !important;
    color: #661421 !important;
  }

  .swal2-confirm {
    background-color: #661421 !important;
    color: white !important;
  }
</style>
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Neuer Teilnehmer / n'])
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      @if(session('import_errors'))
      @foreach(session('import_errors') as $error)
      <div class="alert alert-danger">{{ $error }}</div>
      @endforeach
      @endif
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('store_participant')}}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-primary card-outline" style="position: relative;">
                    <div
                      style="position: absolute; top: 30%; left: 50%; transform: translate(-50%, -50%); width: 40%; height: 50%; border: 2px solid #661421; border-radius: 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 25px; box-sizing: border-box; background-color: white; overflow: visible;">

                      <!-- Text Section -->
                      <div
                        style="max-width: 90%; font-size: 1rem; line-height: 1.4; text-align: center; z-index: 2; font-weight: 600;">
                        Das Muster wurde <span style="color: red;"><b>am 23.10.2023</b></span> erneut geändert, bitte
                        verwenden Sie das Alte nicht mehr und laden Sie das Muster neu
                        herunter.<br>
                        Wenn der Teilnehmer kein Talentlms erhalten soll, können Sie das zusätzliche Feld ignorieren.
                      </div>
                    </div>

                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Neuer Teilnehmer">
                      <div class="card-body box-profile form-group">
                        <a href="#" class="btn btn-outline-primary mb-3" data-toggle="modal"
                          data-target="#bentuzer_erstellen">
                          Video Anschauen
                        </a>
                        <div class="row col-md-12 d-flex justify-content-between">
                          <div>
                            <a type="button" href="{{route('download_muster')}}" name="muster_download"
                              id="muster_download" class="btn btn-success float-left">Muster Herunterladen</a>
                          </div>
                          <div>
                            <div class="mb-2">
                              <label for="participant_required_at">Benötigt bis</label>
                              <!-- required until -->
                              <input type="text" class="form-control startdate" name="participant_required_at" required>
                            </div>
                            <div>
                              <label for="places"> Für Standort:</label>
                              <select class="custom-select" name="place_id" id="participant_place_id">
                                <option selected class="dropdown-menu" value="{{$user->ort}}">{{$user->ort}}</option>
                                <option value="Erfurt">Erfurt</option>
                                <option value="Dresden">Dresden</option>
                                <option value="Leipzig">Leipzig</option>
                                <option value="Chemnitz">Chemnitz</option>
                                <option value="Suhl">Suhl</option>
                                <option value="Berlin-TBR">Berlin-TBR</option>
                                <option value="Berlin-PP">Berlin-PP</option>
                                <option value="Döbeln">Döbeln</option>
                              </select>
                            </div>
                          </div>

                          <div class="row col-md-12 mt-3">
                            <p>Bitte laden Sie das Muster herunter und tragen die Teilnehmer Daten in die entsprechende
                              Zellen ein.</p>
                          </div>
                        </div>
                        <div class="custom-file col-md-4">
                          <input type="file" name="muster">
                        </div>
                        <div>
                          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div><!--end second card -->
              </div>
            </form>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</section>

<!-- Modal -->
<div class="modal fade" id="bentuzer_erstellen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
  data-backdrop="false">
  <div class="modal-dialog" style="max-width: 1200px !important;">
    <div class="modal-content container">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <video fluid="true" id="my-video" class="video-js vjs-theme-city" controls preload="auto" width="600"
          height="400" data-setup="{}">
          <source src="/images/admin_images/Ticket_Erstellen.mp4" type="video/mp4" />
        </video>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $(function () {
    moment.locale('de');
    $('.startdate').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      startDate: moment().add(7, 'day'),
      minDate: moment(),
      minYear: parseInt(moment().format('YYYY')) - 1,
      maxYear: parseInt(moment().format('YYYY')) + 1,
      opens: 'center',
      locale: {
        format: 'DD-MM-YYYY'
      }
    });

  });

  $('body').on('shown.bs.modal', '.modal', function () {
    $('video').trigger('play');
  });
  $('body').on('hidden.bs.modal', '.modal', function () {
    $('video').trigger('pause');
  });


  $(document).ready(function () {
    // check if submitter standort is Berlin
    var submitterStandort = $('input[name="submitter_standort"]').val();
    if (submitterStandort == 'Berlin') {
      Swal.fire({
        title: 'Wählen Sie die Adresse der Teilnehmer',
        input: 'select',
        inputOptions: {
          'Berlin-TBR': 'Berlin-TBR',
          'Berlin-PP': 'Berlin-PP'
        },
        inputPlaceholder: 'Wählen Sie eine Adresse',
        confirmButtonText: 'Auswählen',
        showCancelButton: false,
        allowOutsideClick: false, // do not allow closing by clicking outside
        inputValidator: function (value) {
          return new Promise(function (resolve, reject) {
            if (value !== '') {
              resolve();
            } else {
              resolve('Sie müssen eine Adresse auswählen');
            }
          });
        },
        customClass: {
          popup: 'swal2-popup',
          title: 'swal2-title',
          confirmButton: 'swal2-confirm',
        },
      }).then(function (result) {
        if (result.isConfirmed) {
          // set selected option to chosen value
          $('select[name="place_id"]').val(result.value);
        }
      });
    }
  });

</script>
@endsection