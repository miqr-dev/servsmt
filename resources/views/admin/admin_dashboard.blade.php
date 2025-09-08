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
    <!-- Content Header (Page header) -->
    <section class="content-header text-center">
      <div class="container">
        <div class="row">
          <div class="col-6 mx-auto">
            <h1>{{$dayTerm}}, {{@$user->title}} {{$user->name}}</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
  <section class="content">
    <div class="container-fluid col-lg-12">
      <div class="row">
        <div class="col-10 mx-auto">
          <!-- Profile Image -->
          <div class="card card-primary card-outline">
            <div class="card-body box-profile form-group">
              {!! Form::model($user, ['method' => 'PATCH','route' => ['settings.firstupdate', $user->id]]) !!}
              <!-- Default switch -->
              <div class="checkbox switcher text-right">
                <label for="replication" class="form-group">
                  <input type="checkbox" id="replication" value="">
                  <span><small></small></span>
                  <strong>Replication</strong>
                </label>
              </div>
              <h3 class="profile-username text-center">{{$user->vorname}} {{ $user->name }}
              </h3>
                <p class="text-muted text-center">{{$user->email}}</p>
                <!-- child cards -->
                <div class="row mx-auto">
                  <!-- first card -->
                  <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                      <div class="card-body box-profile form-group">
                        <h3 class="profile-username text-center">Outlook Signatur</h3>
                        <p id="emailHelp" class="form-text text-center mb-3" style="color: A8535F;">Pflichtfelder</p>
                        <!-- Position -->
                        <div class="form-group row border-bottom">
                          <label for="position" class="col-lg-6 col-form-label" style="font-size: larger;"><strong><i class="fas fa-user-tie" style="color: #A8535F;"></i></strong> Tätigkeit </label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="position" value=" {{old('position') ?? $user->position}}">
                            </div>
                        </div>
                        <!-- Abteilung -->
                        <div class="form-group row border-bottom">
                          <label for="abteilung" class="col-lg-6 col-form-label"><strong><i class="fas fa-building fa-lg" style="color: #A8535F;"></i></strong> Abteilung</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="abteilung" value=" {{old('abteilung') ?? $user->abteilung}}">
                            </div>
                        </div>
                        <!-- Rufnummer -->
                        <div class="form-group row border-bottom">
                          <label for="tel" class="col-lg-6 col-form-label"><strong><i class="fas fa-phone-alt fa-lg" style="color: #A8535F;"></i></strong> Rufnummer</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="tel" value=" {{old('tel') ?? $user->tel}}">
                            </div>
                        </div>
                      <!-- /.card-body -->
                        <!-- FAX -->
                        <div class="form-group row border-bottom">
                          <label for="fax" class="col-lg-6 col-form-label"><strong><i class="fas fa-fax fa-lg" style="color: #A8535F;"></i></strong> Fax</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="fax" value=" {{old('fax') ?? $user->fax}}">
                            </div>
                        </div>
                        <!-- Address -->
                        <div class="form-group row border-bottom mb-4">
                          <label for="straße" class="col-lg-6 col-form-label"><strong><i class="fas fa-map-marked-alt fa-lg" style="color: #A8535F;"></i></strong> Adresse</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="ort" value=" {{old('ort') ?? $user->ort}}">
                              <input type="text" class="form-control mb-2" name="straße" value=" {{old('straße') ?? $user->straße}}">
                              <input type="text" class="form-control mb-2" name="plz" value=" {{old('plz') ?? $user->plz}}">
                            </div>
                        </div>
                      <!-- /.card-body -->
                      </div>
                    </div>
                  </div>
                  <!--end first card -->
                  <!-- second card -->
                  <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                      <div class="card-body box-profile form-group">
                        <h3 class="profile-username text-center">Privat</h3>
                        <small class="form-text text-muted text-center mb-3">Optional</small>
                        <!-- Vorname -->
                        <div class="form-group row border-bottom">
                          <label for="mobil" class="col-lg-6 col-form-label"><strong><i class="far fa-user fa-lg" style="color: #A8535F;"></i></strong> Anrede / Akademischer Grad</label>
                          <div class="col-lg-6">
                            <select class="form-control mb-2" name="title">
                              @if(isset($user->title))
                              <option value="{{$user->title}}" selected>{{$user->title}}</option>
                              <option value="Frau">Frau</option>
                              <option value="Herr">Herr</option>
                              <option value="Dr.">Dr.</option>
                              <option value="Prof.">Prof.</option>
                              <option value="Prof.Dr.">Prof.Dr.</option>
                              @else
                              <option value="" selected disabled>Bitte Wählen</option>
                              <option value="Frau">Frau</option>
                              <option value="Herr">Herr</option>
                              <option value="Dr.">Dr.</option>
                              <option value="Prof.">Prof.</option>
                              <option value="Prof.Dr.">Prof.Dr.</option>
                              @endif
                            </select>
                          </div>
                          <label for="vorname" class="col-lg-6 col-form-label"><strong><i class="far fa-user fa-lg" style="color: #A8535F;"></i></strong> Vorname</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="vorname" value=" {{old('vorname') ?? $user->vorname}}">
                            </div>
                          <label for="name" class="col-lg-6 col-form-label"><strong><i class="far fa-user fa-lg" style="color: #A8535F;"></i></strong> Name</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="name" value=" {{old('name') ?? $user->name}}">
                            </div>
                        </div>
                        <!-- Mobil -->
                        <div class="form-group row border-bottom">
                          <label for="mobil" class="col-lg-6 col-form-label"><strong><i class="fas fa-mobile-alt fa-lg" style="color: #A8535F;"></i></strong> Mobiltelefon</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="mobil" value=" {{old('mobil') ?? $user->mobil}}">
                            </div>
                            <label for="privat" class="col-lg-6 col-form-label"><strong><i class="fas fa-mobile fa-lg" style="color: #A8535F;"></i></strong> Tel. Privat</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="privat" value=" {{old('privat') ?? $user->privat}}">
                            </div>
                            <label for="email_privat" class="col-lg-6 col-form-label"><strong><i class="fas fa-at fa-lg" style="color: #A8535F;"></i></strong> Email-Privat</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="email_privat" value=" {{old('email_privat') ?? $user->email_privat}}">
                            </div>
                        </div>
                        <!-- abschluss -->
                        <div class="form-group row border-bottom">
                          <label for="abschluss" class="col-lg-6 col-form-label"><strong><i class="fas fa-graduation-cap fa-lg" style="color: #A8535F;"></i></strong> Abschluss</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="abschluss" value=" {{old('abschluss') ?? $user->abschluss}}">
                            </div>
                            <label for="office " class="col-lg-6 col-form-label"><strong><i class="fas fa-briefcase fa-lg" style="color: #A8535F;"></i></strong> BusinessUnit</label>
                            <div class="col-lg-6">
                              <input type="text" class="form-control mb-2" name="office" value=" {{old('office') ?? $user->office}}">
                              <input type="hidden" name="username" value="{{$user->username}}">
                            </div>
                        </div>
                        <!-- end inputs -->
                      </div>
                    </div>
                  </div>
                  <!--end second card -->
              </div>
              <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
              {!! Form::close() !!}
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
    <!-- /.content -->
  <!-- /.content-wrapper -->



@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
@endsection
