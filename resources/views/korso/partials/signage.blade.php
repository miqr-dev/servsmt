@php
  $signageItems = [
    'Beklebung' => ['label' => 'Beklebung', 'qty' => 1],
    'Beschilderung' => ['label' => 'Beschilderung', 'qty' => 1],
    'Plakate' => ['label' => 'Plakate', 'qty' => 1],
  ];
@endphp

<div class="tab-pane fade" id="signage-options" role="tabpanel">
  @foreach($signageItems as $name => $data)
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
