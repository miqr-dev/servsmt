<div class="col-lg-2">
  <div class="card card-thirdary card-outline">
    <div class="card-body box-profile form-group">
      <div class="row d-none">
        <div class="form-group col-md-6">
          <label for="submitter"> Erstellt von</label>
          <input type="text" class="form-control" name="submitter_name" value="{{$user->username}}" readonly>
          <input type="hidden" class="form-control" name="submitter" value="{{$user->id}}" readonly>
        </div>
        <div class="form-group col-md-6">
          <label for="submit_date">Erstellt Am</label>
          <input type="text" class="form-control" value="{{ $now }}" readonly>
        </div>
      </div>
      <div class="row d-none">
        <div class="form-group col-md-6">
          <label for="submitter_standort"> Standort</label>
          <input type="text" class="form-control" name="submitter_standort" value="{{$user->ort}}" readonly>
        </div>
        <div class="form-group col-md-6">
          <label for="submitter_adresse">Adresse</label>
          <input type="text" class="form-control" name="submitter_adresse" value="{{$user->straße}}" readonly>
        </div>
      </div>
      <div class="row">
        @if(auth()->user()->sekGroups->isNotEmpty())
        <div class="form-group">
          <label>Einreichen als</label>

          <div class="custom-control custom-radio">
            <input type="radio" id="persRadio" name="sek_group_id" class="custom-control-input" value="" checked>
            <label class="custom-control-label" for="persRadio">
              Persönlich
            </label>
          </div>

          <div class="custom-control custom-radio">
            <input type="radio" id="sekRadio" name="sek_group_id" class="custom-control-input"
              value="{{ auth()->user()->sekGroups->first()->id }}">
            <label class="custom-control-label" for="sekRadio">
              Sekretariat ({{ auth()->user()->sekGroups->first()->name }})
            </label>
          </div>
        </div>
        @endif
      </div>
      <div class="row">
        <div class="form-group col-md-12">
          <label for="priority"> Priorität</label>
          <select class="custom-select" name="priority" id="ticket_type">
            <option selected class="dropdown-menu" value="2">Normal</option>
            <!-- <option value="1">Niedrig</option> -->
            <option value="2">Normal</option>
            <option value="3">Hoch</option>
          </select>
        </div>
        <div class="form-group col-md-12">
          <label for="tel_number"> Telefon</label>
          <input type="text" class="form-control" name="tel_number" value="{{$user->tel}}" readonly>
        </div>
        <!-- @if($isException) -->
        <div class="form-group col-md-12">
          <label for="submitter_standort_exception"> Standort ändern</label>
          <select class="custom-select" name="submitter_standort_exception" id="submitter_standort_exception">
            <option value="Dresden" @if(auth()->user()->ort == 'Dresden') selected @endif>Dresden</option>
            <option value="Erfurt" @if(auth()->user()->ort == 'Erfurt') selected @endif>Erfurt</option>
            <option value="Suhl" @if(auth()->user()->ort == 'Suhl') selected @endif>Suhl</option>
            <option value="Leipzig" @if(auth()->user()->ort == 'Leipzig') selected @endif>Leipzig</option>
            <option value="Chemnitz" @if(auth()->user()->ort == 'Chemnitz') selected @endif>Chemnitz</option>
            <option value="Berlin" @if(auth()->user()->ort == 'Berlin') selected @endif>Berlin</option>
            <option value="Döbeln" @if(auth()->user()->ort == 'Döbeln') selected @endif>Döbeln</option>
            <option value="Riesa" @if(auth()->user()->ort == 'Riesa') selected @endif>Riesa</option>
          </select>
        </div>
        <!-- @endif -->
      </div>
      <div id="output"></div>
      &nbsp;<i class="fa-solid fa-thumbtack fa-lg" style="color: #65A30D;"></i> <span> Pflichtfeld</span>
      <!-- /.card-body -->
    </div>
  </div>
</div>