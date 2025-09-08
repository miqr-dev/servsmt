@extends('layouts.admin_layout.admin_layout')

@section('content')
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <!-- child cards -->
            <div class="row mx-auto">
              <!--end Submitter Section -->
              <!-- second card -->
              <div class="col-lg-3">
                <div class="card card-primary card-outline">
                  <div class="card-body box-profile form-group">       
                    <div class="form-group col-md-12">
                      <label for="datum_berlin">Berlin</label>
                      <input type="text" class="form-control date" id="datum_berlin" value="{{$datum->formatted_berlin}}" name="datum_berlin" required>
                    </div>           
                    <div class="form-group col-md-12">
                      <label for="datum_berlinii">Berlin II</label>
                      <input type="text" class="form-control date" id="datum_berlinii" value="{{$datum->formatted_berlinii}}" name="datum_berlinii" required>
                    </div>           
                    <div class="form-group col-md-12">
                      <label for="datum_chemnitz">Chemnitz</label>
                      <input type="text" class="form-control date" id="datum_chemnitz" value="{{$datum->formatted_chemnitz}}" name="datum_chemnitz" required>
                    </div>           
                    <div class="form-group col-md-12">
                      <label for="datum_dresden">Dresden</label>
                      <input type="text" class="form-control date" id="datum_dresden" value="{{$datum->formatted_dresden}}" name="datum_dresden" required>
                    </div>           
                    <div class="form-group col-md-12">
                      <label for="datum_leipzig">Leipzig</label>
                      <input type="text" class="form-control date" id="datum_leipzig" value="{{$datum->formatted_leipzig}}" name="datum_leipzig" required>
                    </div>           
                    <div class="form-group col-md-12">
                      <label for="datum_suhl">Suhl</label>
                      <input type="text" class="form-control date" id="datum_suhl" value="{{$datum->formatted_suhl}}" name="datum_suhl" required>
                    </div>           
                  </div>
                </div>
              </div><!--end second card -->
            </div>
            <!-- </form> -->
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</section>

@endsection

@section('script')
<script>

// update locale to de and customize the MMM, MMMM translation
  moment.updateLocale("de", {
  months : ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
  monthsShort : ['Jan', 'Feb', 'März', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez']
});
$(function() {
  $('.date').daterangepicker({
		singleDatePicker: true,
		showDropdowns: true,
		minYear: parseInt(moment().format('YYYY'))-1,
		maxYear: parseInt(moment().format('YYYY'))+1

  });
});

$.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});

$(document).ready(function(){
  $(document).on('change', '#datum_berlin', function() {
    let datum_berlin = $(this).val();
   $.ajax({
    type:"post",
    url: "{{route('standortbesuch_berlin')}}",
    data: {"datum_berlin":datum_berlin},
    success:function(result){
    }
   });
  });
  $(document).on('change', '#datum_berlinii', function() {
    let datum_berlinii = $(this).val();
   $.ajax({
    type:"post",
    url: "{{route('standortbesuch_berlinii')}}",
    data: {"datum_berlinii":datum_berlinii},
    success:function(result){
    }
   });
  });
  $(document).on('change', '#datum_chemnitz', function() {
    let datum_chemnitz = $(this).val();
   $.ajax({
    type:"post",
    url: "{{route('standortbesuch_chemnitz')}}",
    data: {"datum_chemnitz":datum_chemnitz},
    success:function(result){
    }
   });
  });
  $(document).on('change', '#datum_dresden', function() {
    let datum_dresden = $(this).val();
   $.ajax({
    type:"post",
    url: "{{route('standortbesuch_dresden')}}",
    data: {"datum_dresden":datum_dresden},
    success:function(result){
    }
   });
  });
  $(document).on('change', '#datum_leipzig', function() {
    let datum_leipzig = $(this).val();
   $.ajax({
    type:"post",
    url: "{{route('standortbesuch_leipzig')}}",
    data: {"datum_leipzig":datum_leipzig},
    success:function(result){
    }
   });
  });
  $(document).on('change', '#datum_suhl', function() {
    let datum_suhl = $(this).val();
   $.ajax({
    type:"post",
    url: "{{route('standortbesuch_suhl')}}",
    data: {"datum_suhl":datum_suhl},
    success:function(result){
    }
   });
  });
})


</script>
@endsection