@php
  $giveaways = [
    'City_Cards_Do_you_speak_German' => ['label' => 'City Cards – „Do you speak German“', 'qty' => 50, 'hasWarning' => false],
    'City_Cards_Salad' => ['label' => 'City Cards – Salad', 'qty' => 50, 'hasWarning' => false],
    'City_Cards_Sauerkraut' => ['label' => 'City Cards – Sauerkraut', 'qty' => 50, 'hasWarning' => false],
    'City_Cards_Smiley' => ['label' => 'City Cards – Smiley', 'qty' => 50, 'hasWarning' => false],
    'City_Cards_Egg' => ['label' => 'City Cards – Egg', 'qty' => 50, 'hasWarning' => false],
    'Tragetaschen' => ['label' => 'Tragetaschen Baumwolle', 'qty' => 50, 'hasWarning' => true],
    'Einkaufswagenloeser' => ['label' => 'Einkaufswagenlöser', 'qty' => 50, 'hasWarning' => true],
    'Pflastermaeppchen' => ['label' => 'Pflastermäppchen', 'qty' => 1, 'hasWarning' => true],
    'Fruchtgummi' => ['label' => 'Fruchtgummi', 'qty' => 250, 'hasWarning' => true],
    'Kugelschreiber' => ['label' => 'Kugelschreiber Rot', 'qty' => 100, 'hasWarning' => true],
  ];

  $beforeWarning = array_filter($giveaways, function ($item) {
    return $item['hasWarning'] === false;
  });

  $afterWarning = array_filter($giveaways, function ($item) {
    return $item['hasWarning'] === true;
  });
@endphp

<div class="tab-pane fade" id="giveaways-options" role="tabpanel">
  {{-- ✅ City Cards (no warning) --}}
  @foreach($beforeWarning as $name => $data)
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

  {{-- ⚠️ Warning Divider --}}
  @if(count($afterWarning))
  <div class="alert alert-danger my-3">
    <strong>ACHTUNG:</strong> Diese Artikel können nur quartalsweise bestellt werden. Bitte bestellen Sie vorausschauend!
  </div>
  @endif

  @php
use Carbon\Carbon;

$grayOutGiveaways = [
    'Einkaufswagenloeser' => ['from' => '2025-11-01', 'to' => '2025-12-01'],
    'Pflastermaeppchen'   => ['from' => '2025-11-01', 'to' => '2025-12-01'],
    'Fruchtgummi'         => ['from' => '2025-11-01', 'to' => '2025-12-01'],
];
$now = Carbon::now();
@endphp

  {{-- 📦 All other items with warning --}}
@foreach($afterWarning as $name => $data)
  @php
    $isGrayed = false;
    $tooltip = '';
    if(isset($grayOutGiveaways[$name])) {
        $range = $grayOutGiveaways[$name];
        $from = \Carbon\Carbon::parse($range['from']);
        $to = \Carbon\Carbon::parse($range['to']);
        $isGrayed = !$now->between($from, $to);
        $tooltip = "Verfügbar vom " . $from->format('d.m.Y') . " bis " . $to->format('d.m.Y');
    }
  @endphp
  <div class="form-check d-flex align-items-center mb-2">
    <div class="flex-grow-1">
      <input class="form-check-input item-checkbox" type="checkbox" name="{{ $name }}"
        data-has-quantity-input="true" id="{{ $name }}"
        @if($isGrayed) disabled style="pointer-events:none;opacity:.6;" data-toggle="tooltip" title="{{ $tooltip }}" @endif
      >
      <label class="form-check-label" for="{{ $name }}"
        @if($isGrayed) style="color:#888;" data-toggle="tooltip" title="{{ $tooltip }}" @endif
      >
        {{ $data['label'] }}
        @if($isGrayed)
          <span class="ml-1" style="font-size:90%;color:#888;" data-toggle="tooltip" title="{{ $tooltip }}">
            <i class="fas fa-info-circle"></i>
          </span>
        @endif
      </label>
    </div>
    <div class="flex-shrink-0">
      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
        name="{{ $name }}_qty" min="1" value="{{ $data['qty'] }}" style="width: 60px; display: none;" @if($isGrayed) disabled @endif>
    </div>
  </div>
@endforeach
</div>
