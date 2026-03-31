<div class="col-lg-4">
  @php
    $availableSubmitterUsers = $users ?? \App\User::select(
      'id',
      'vorname',
      'name',
      'username',
      'ort',
      'straße',
      'tel'
    )->get()->toArray();
  @endphp
  <div class="card card-primary card-outline">
    <div class="card-body box-profile form-group">
      <div class="row">
        @if(auth()->user()->hasRole('Super_Admin'))
        <!-- <div class="form-group col-md-12">
          <button type="submit" class="btn btn-success col-md-12">Erledigt</button>
        </div> -->
        @endif
        <div class="form-group col-md-6">
          <label for="submitter"> Erstellt von</label>
          @if(auth()->user()->hasRole('Super_Admin'))
          <input type="text" class="form-control" id="submitter_search" list="submitter_list"
            value="{{ trim(($user->vorname ?? '').' '.($user->name ?? '')) }} ({{$user->username}})" required>
          <datalist id="submitter_list">
            @foreach($availableSubmitterUsers as $availableUser)
            <option
              value="{{ (trim(($availableUser['vorname'] ?? '').' '.($availableUser['name'] ?? '')) ?: ($availableUser['username'] ?? 'Unbekannt')) .' ('.($availableUser['username'] ?? 'kein-username').')' }}"
              data-id="{{$availableUser['id']}}"
              data-fullname="{{ trim(($availableUser['vorname'] ?? '').' '.($availableUser['name'] ?? '')) }}"
              data-city="{{$availableUser['ort'] ?? ''}}"
              data-address="{{$availableUser['straße'] ?? ''}}"
              data-phone="{{$availableUser['tel'] ?? ''}}">
            </option> 
            @endforeach
          </datalist>
          <input type="hidden" class="form-control" id="submitter" name="submitter" value="{{$user->id}}" readonly>
          @else
          <input type="text" class="form-control" name="submitter_name" value="{{@$user->vorname}} {{@$user->name}}" readonly>
          <input type="hidden" class="form-control" name="submitter" value="{{$user->id}}" readonly>
          @endif
        </div>
        <div class="form-group col-md-6">
          <label for="submit_date">Erstellt Am</label>
          <input type="text" class="form-control" name="submit_date" value="{{ $now }}" readonly>
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-6">
          <label for="submitter_standort"> Standort</label>
          <input type="text" class="form-control" id="submitter_standort" name="submitter_standort" value="{{$user->ort}}" readonly>
        </div>
        <div class="form-group col-md-6">
          <label for="submitter_adresse">Adresse</label>
          <input type="text" class="form-control" id="submitter_adresse" name="submitter_adresse" value="{{$user->straße}}" readonly>
        </div>
      </div>
      <div class="row">
        @if(auth()->user()->hasRole('Super_Admin'))
        <div class="form-group col-md-12">
          <label for="priority"> Priorität</label>
          <select class="custom-select" name="priority" id="ticket_type">
            <option selected class="dropdown-menu" value="2">Normal</option>
            <option value="1">Niedrig</option>
            <option value="2">Normal</option>
            <option value="3">Hoch</option>
          </select>
        </div>
        @else
        <div class="form-group col-md-12">
          <label for="priority"> Priorität</label>
          <select class="custom-select" name="priority" id="ticket_type">
            <option selected class="dropdown-menu" value="2">Normal</option>
            <option value="2">Normal</option>
          </select>
        </div>
        @endif
        <div class="form-group col-md-12">
          <label for="tel_number"> Telefon</label>
          <input type="text" class="form-control" id="tel_number" name="tel_number" value="{{$user->tel}}" readonly>
        </div>
        <div class="form-group col-md-12">
          <label for="custom_tel_number"> Aktuelle Rufnummer <i class="fas fa-question" style="color: #661421;" data-toggle="tooltip" data-placement="top" title="Telefonnummer unter der Sie erreichbar sind" ></i> &nbsp;</label>
          <input type="text" class="form-control" name="custom_tel_number" >
        </div>
      </div>
      <div id="output"></div>
      &nbsp;<i class="fas fa-feather-alt fa-lg" style="color: #661421;"></i> <span>Pflichtfeld</span>
    <!-- /.card-body -->
    </div>
  </div>
</div>

@if(auth()->user()->hasRole('Super_Admin'))
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const submitterInput = document.getElementById('submitter_search');
    const submitterOptions = document.querySelectorAll('#submitter_list option');

    if (!submitterInput || !submitterOptions.length) {
      return;
    }

    const syncSubmitterDetails = function () {
      const selectedOption = Array.from(submitterOptions).find((option) => option.value === submitterInput.value);
      if (!selectedOption) {
        document.getElementById('submitter').value = '';
        return;
      }

      document.getElementById('submitter').value = selectedOption.dataset.id || '';
      document.getElementById('submitter_standort').value = selectedOption.dataset.city || '';
      document.getElementById('submitter_adresse').value = selectedOption.dataset.address || '';
      document.getElementById('tel_number').value = selectedOption.dataset.phone || '';
    };

    submitterInput.addEventListener('change', syncSubmitterDetails);
    submitterInput.addEventListener('blur', syncSubmitterDetails);
    syncSubmitterDetails();
  });
</script>
@endif
