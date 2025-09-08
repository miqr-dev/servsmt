@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Softwareanfrage'])
<!-- Main content -->
  <section class="content">
    <div class="container-fluid col-lg-12">
      <div class="row">
        <div class="col-12 mx-auto">
          <!-- Profile Image -->
          <div class="card card-primary card-outline">
            <div class="card-body box-profile form-group">
              <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
                @csrf
                <!-- child cards -->
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                  <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-primary card-outline">
                    <div id="underform">
                     <input type="hidden" name="problem_type" value="Aktivieren">
                    <div class="card-body box-profile form-group">       
                      <div class="row col-md-12">
                        <div class="form-group col-md-6">
                          <label for="searchcomputer"> Welcher Rechner &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                          <select class="custom-select form-control mb-2 searchcomputer" name="searchcomputer" required>
                          <option class="form-control" value=""></option>
                          @foreach($computers as $computer)
                            <option class="form-control" value="{{$computer['id']}}">{{$computer['gname']}}</option>
                          @endforeach
                          </select>
                        </div>
                          <div class="form-group col-md-6">
                            <label for="searchsoftware"> Welche App &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                          <select class="custom-select form-control mb-2 searchsoftware" name="searchsoftware" required>
                          <option class="form-control" value=""></option>
                            <option class="form-control" value="1">Teamviewer</option>
                            <option class="form-control" value="2">FireFox</option>
                            <option class="form-control" value="3">Chrome</option>
                            <option class="form-control" value="4">another one</option>
                          </select>
                          <div>
                            <p><small style="color:#661421;">App nicht in der Liste? schreiben 'rein' und drücken Sie die Eingabetaste</small></p>  
                          </div>
                          </div>
                          <div class="form-group col-md-6 col-lg-12">
                            <label for="notizen"> Notizen</label>
                            <textarea type="text" name="notizen" class="form-control notizen" ></textarea>
                          </div>
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
        </div> <!-- /.col -->
      </div> <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section><!-- /.content -->



@endsection

@section('script')
<script>
  $.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});


    $(".searchcomputer").select2({
      placeholder: 'Bitte Wählen',
    });

    $('.searchsoftware').select2({
      placeholder: 'Bitte Wählen',
      allowClear: false,
      tags: true
    });

    $('.notizen').summernote({
    height:150,
    lang: 'de-DE'
    });

</script>
@endsection





