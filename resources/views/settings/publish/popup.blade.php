@extends('layouts.admin_layout.admin_layout')
<style>
  div.checkbox.switcher label, div.radio.switcher label {
	 padding: 0;
}
 div.checkbox.switcher label *, div.radio.switcher label * {
	 vertical-align: middle;
}
 div.checkbox.switcher label input, div.radio.switcher label input {
	 display: none;
}
 div.checkbox.switcher label input + span, div.radio.switcher label input + span {
	 position: relative;
	 display: inline-block;
	 margin-right: 10px;
	 width: 56px;
	 height: 28px;
	 background: #f2f2f2;
	 border: 1px solid #eee;
	 border-radius: 50px;
	 transition: all 0.3s ease-in-out;
}
 div.checkbox.switcher label input + span small, div.radio.switcher label input + span small {
	 position: absolute;
	 display: block;
	 width: 50%;
	 height: 100%;
	 background: #fff;
	 border-radius: 50%;
	 transition: all 0.3s ease-in-out;
	 left: 0;
}
 div.checkbox.switcher label input:checked + span, div.radio.switcher label input:checked + span {
	 background: #681A25;
	 border-color: #681A25;
}
 div.checkbox.switcher label input:checked + span small, div.radio.switcher label input:checked + span small {
	 left: 50%;
}

 
</style>

@section('content')
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{ route('popup.update',$news->id) }}" method="POST">
              @csrf
              @method('PUT')
            <!-- child cards -->
            <div class="row mx-auto">
              <!--end Submitter Section -->
              <!-- second card -->
              <div class="col-lg-6">
                <div class="card card-primary card-outline">
                  <div class="card-body box-profile form-group">       
                    <div class="row col-md-12">
                      <div class="form-group col-md-6">
                        <label for="title"> Title</label>
                        <input type="text" name="title" class="form-control" required autocomplete="false" value="{{@$news->title}}">
                      </div>
                      <div class="custom-control custom-checkbox col-md-3">
                        <input type="checkbox" class="custom-control-input" id="isPublished" name="isPublished" 
                        {{ $news->isPublished === 'on' ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="isPublished">Publish</label>
                      </div>
                      <div class="col-md-3">
                        @if($news->isPublished === 'on')
                        <div>
                          <div class="spinner-grow spinner-grow-sm text-success">
                          </div>
                          <span class="text-success ml-2">Active</span>
                        </div>
                        @else
                        <div>
                          <div class="spinner-grow spinner-grow-sm text-danger">
                          </div>
                          <span class="text-danger ml-2">Inactive</span>
                        </div>
                        @endif
                      </div>
                    </div>
                    <div class="row col-md-12">  
                      <div class="form-group col-md-12">
                        <label for="body"> Body </label>
                        <textarea type="text" name="body" class="form-control" required autocomplete="false">{{@$news->body}}</textarea>
                      </div>
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