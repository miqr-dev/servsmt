@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Peripherie-anfrage'])


<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
              @csrf
              <input type="hidden" name="problem_type" value="Peripherie Anfrage">
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-primary card-outline">
                    <div class="card-body box-profile form-group">
                      <div class="row">
                        <div class="col-md-12 d-flex justify-content-between">
                          <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="keyboard" name="keyboard">
                            <label class="custom-control-label" for="keyboard">Tastatur</label>
                          </div>
                          <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="mouse" name="mouse">
                            <label class="custom-control-label" for="mouse">Maus</label>
                          </div>
                          <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="speaker" name="speaker">
                            <label class="custom-control-label" for="speaker">Lautsprecher</label>
                          </div>
                          <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="headset" name="headset">
                            <label class="custom-control-label" for="headset">Kopfhörer</label>
                          </div>
                          <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="webcam" name="webcam">
                            <label class="custom-control-label" for="webcam">Webcam</label>
                          </div>
                          <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="other" name="other">
                            <label class="custom-control-label" for="other">Sonstiges</label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div id="underform">
                      <div class="card-body box-profile form-group">       
                        <div class="row col-md-12">
                          <div class="form-group col-md-6">
                            <label for="searchcomputer"> Welcher Rechner &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                            <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer" required>
                              <option class="form-control" value="">Bitte Wählen</option>
                              @foreach($computers as $computer)
                                <option class="form-control" value="{{$computer['id']}}">{{$computer['gname']}}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="form-group col-md-6">
                            <div class="card-flyer2 text-center">
                              <div class="text-box2">
                                <div class="text-container2">
                                  <img src="/images/admin_images/info_400_2.png" style="max-width:75px;"  alt="" />
                                    <p class="text-info">Anzahl über <strong>1</strong>? Bitte schreiben Sie die benötigte<br> Anzahl und den Grund in das Notizfeld.</p>
                                    <p class="text-info"><strong>Bildschirm ?</strong> 
                                      <i class="fas fa-long-arrow-alt-right" style="color:green;"></i> <a href="{{route('hardwareRequest')}}">Hardware-Anfrage</a> 
                                    </p>
                                </div>
                              </div>
                            </div>
                          </div>
                          @include('tickets.layout_ticket.note',['discription'=>'Beschreibung'])
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
    </div> <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
@endsection

@section('script')
<script>
  $.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});


</script>
@endsection





