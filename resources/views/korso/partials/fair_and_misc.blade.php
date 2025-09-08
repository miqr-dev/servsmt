@php
  $messeItems = [
    'Anmeldung_Messe' => ['label' => 'Anmeldung Messe', 'qty' => 1],
    'Ausstattung_Messe' => ['label' => 'Ausstattung Messe', 'qty' => 1],
    'Rollup_Anzahl' => ['label' => 'Rollup Anzahl', 'qty' => 1],
    'Plakate_Anzahl' => ['label' => 'Plakate Anzahl', 'qty' => 1],
    'Messestand' => ['label' => 'Messestand', 'qty' => 1],
    'Beach_Flag' => ['label' => 'Beach Flag', 'qty' => 1],
    'Sonstiges' => ['label' => 'Sonstiges', 'qty' => 1],
  ];
@endphp

<div class="tab-pane fade" id="messe-options" role="tabpanel">
  @foreach($messeItems as $name => $data)
  <div class="form-check d-flex align-items-center mb-2">
    <div class="flex-grow-1">
      <input class="form-check-input item-checkbox" type="checkbox" name="{{ $name }}"
        data-has-quantity-input="true" id="{{ $name }}">
      <label class="form-check-label" for="{{ $name }}">{{ $data['label'] }}</label>
    </div>
    <div class="flex-shrink-0">
      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
        name="{{ $name }}_qty" min="1" value="{{ $data['qty'] }}" style="width: 60px; display: none;">
    </div>
  </div>
  @endforeach
</div>
