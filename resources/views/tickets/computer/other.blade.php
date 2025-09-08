@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Sonstiges'])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
            @csrf
            <input type="hidden" name="problem_type" value="PC Laptop Sonstiges">
            <!-- child cards -->
            <div class="row mx-auto">
              <!-- Submitter Section layout_ticket submitter.blade.php -->
              @include('tickets.layout_ticket.submitter')
              <!--end Submitter Section -->
              <!-- second card -->
              <div class="col-lg-8">
                <div class="card card-primary card-outline">
                  <div class="card-body box-profile form-group">       
                    <div class="row col-md-12">
                      <div class="form-group col-md-6">
                        <label for="pclaptopsonstiges"> Betreff &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i></label>
                        <input type="text" name="pclaptopsonstiges" id="pclaptopsonstiges" class="form-control" required autocomplete="false">
                      </div>
                      @include('tickets.layout_ticket.note',['discription'=>'Problembeschreibung'])
                    </div>                  
                    <div>
                      <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
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
@endsection
@section('script')
@endsection




