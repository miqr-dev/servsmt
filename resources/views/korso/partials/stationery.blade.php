@php
$items = [
'Visitenkarten' => ['label' => 'Visitenkarten', 'qty' => 100, 'hasWarning' => false],
'Glueckwunschkarte_Alles_Gute' => ['label' => 'Glückwunschkarte A6 – „Alles Gute“', 'qty' => 50, 'hasWarning' => false],
'Glueckwunschkarte_blanco' => ['label' => 'Glückwunschkarte A6 – blanco', 'qty' => 50, 'hasWarning' => false],
'GA_Mappen' => ['label' => 'GA – Mappen', 'qty' => 100, 'hasWarning' => true],
'Zeugnismappe' => ['label' => 'Zeugnismappe', 'qty' => 100, 'hasWarning' => true],
'Zeugnismappe_Deutschkurse' => ['label' => 'Zeugnismappe Deutschkurse', 'qty' => 100, 'hasWarning' => true],
'Zeugnispapier' => ['label' => 'Zeugnispapier', 'qty' => 200, 'hasWarning' => true],
'A5_Ringblock' => ['label' => 'A5 Ringblock', 'qty' => 50, 'hasWarning' => true],
'A4_Schreibblock_Streifen' => ['label' => 'A4 Schreibblock Streifen', 'qty' => 100, 'hasWarning' => true],
// 'A4_Schreibblock_Sprache' => ['label' => 'A4 Schreibblock Sprache', 'qty' => 100, 'hasWarning' => true],
'USB_Stick' => ['label' => 'USB-Stick', 'qty' => 20, 'hasWarning' => true],
'Notizblock_PostIt_Stift' => ['label' => 'Notizblock mit Post it + Stift', 'qty' => 50, 'hasWarning' => true],
'Block_A6' => ['label' => 'Block A6', 'qty' => 100, 'hasWarning' => true],
'Versandtasche_Fenster' => [
'label' => 'Versandtasche mit Fenster',
'hasWarning' => true,
'children' => [
'Versandtasche_Fenster_C4' => ['label' => 'C4', 'qty' => 100],
'Versandtasche_Fenster_C5' => ['label' => 'C5', 'qty' => 100],
'Versandtasche_Fenster_DL' => ['label' => 'DL', 'qty' => 100],
],
],
'Versandtasche_ohne_Fenster' => [
'label' => 'Versandtasche ohne Fenster',
'hasWarning' => true,
'children' => [
'Versandtasche_ohne_Fenster_C4' => ['label' => 'C4', 'qty' => 100],
'Versandtasche_ohne_Fenster_C5' => ['label' => 'C5', 'qty' => 100],
'Versandtasche_ohne_Fenster_DL' => ['label' => 'DL', 'qty' => 100],
],
],

];

$beforeWarning = array_filter($items, function ($item) {
return $item['hasWarning'] === false;
});

$afterWarning = array_filter($items, function ($item) {
return $item['hasWarning'] === true;
});
@endphp

<div class="tab-pane fade" id="stationery-options" role="tabpanel">
  {{-- Items BEFORE warning --}}
  @foreach($beforeWarning as $name => $data)
  <div class="form-check d-flex align-items-center mb-2">
    <div class="flex-grow-1">
      @if($name === 'Visitenkarten')
      <input class="form-check-input item-checkbox" type="checkbox" name="{{ $name }}" data-has-quantity-input="true"
        data-show-extra="visitenkarten-extra" id="{{ $name }}">
      @else
      <input class="form-check-input item-checkbox" type="checkbox" name="{{ $name }}" data-has-quantity-input="true"
        id="{{ $name }}">
      @endif
      <label class="form-check-label" for="{{ $name }}">{{ $data['label'] }}</label>
    </div>
    <div class="flex-shrink-0">
      <input class="form-control form-control-sm ml-3 quantity-input" type="number" name="{{ $name }}_qty" min="1"
        value="{{ $data['qty'] }}" style="width: 60px; display: none;">
    </div>
    @if($name === 'Visitenkarten')
    <div class="visitenkarten-extra mt-2 pl-4" style="display: none;">
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="visitenkarte_name">Name, Vorname</label>
          <input type="text" class="form-control" name="visitenkarte_name">
        </div>
        <div class="form-group col-md-6">
          <label for="visitenkarte_adresse">Adresse</label>
          <input type="text" class="form-control" name="visitenkarte_adresse">
        </div>
        <div class="form-group col-md-6">
          <label for="visitenkarte_email">E-Mail</label>
          <input type="email" class="form-control" name="visitenkarte_email">
        </div>
        <div class="form-group col-md-6">
          <label for="visitenkarte_telephone">Telefon</label>
          <input type="text" class="form-control" name="visitenkarte_telephone">
        </div>
        <div class="form-group col-md-6">
          <label for="visitenkarte_position">Fachbereich / Position</label>
          <input type="text" class="form-control" name="visitenkarte_position">
        </div>
        <div class="form-group col-md-6">
          <label for="visitenkarte_fax">Fax</label>
          <input type="text" class="form-control" name="visitenkarte_fax">
        </div>
      </div>
    </div>
    @endif

  </div>
  @endforeach

  {{-- Warning --}}
  @if(count($afterWarning))
  <div class="alert alert-danger my-3">
    <strong>ACHTUNG:</strong> Diese Artikel können nur quartalsweise bestellt werden. Bitte bestellen Sie
    vorausschauend!
  </div>
  @endif

  {{-- Items AFTER warning --}}
  @php
  use Carbon\Carbon;

  // Items to gray out with their date windows
  $grayOutItems = [
  'A5_Ringblock' => ['from' => '2025-11-01', 'to' => '2025-12-31'],
  'USB_Stick' => ['from' => '2025-11-01', 'to' => '2025-12-31'],
  'Notizblock_PostIt_Stift' => ['from' => '2025-11-01', 'to' => '2025-12-01'],
  ];
  $now = Carbon::now();
  @endphp

  @foreach($afterWarning as $name => $data)
  @if(isset($data['children']))
  <div class="mb-2">
    <div class="font-weight-bold">{{ $data['label'] }}</div>
    <div class="ml-3">
      @foreach($data['children'] as $childName => $child)
      <div class="form-check d-flex align-items-center mb-1">
        <input class="form-check-input item-checkbox" type="checkbox" name="{{ $childName }}"
          data-has-quantity-input="true" id="{{ $childName }}">
        <label class="form-check-label" for="{{ $childName }}">{{ $child['label'] }}</label>
        <input class="form-control form-control-sm ml-3 quantity-input" type="number" name="{{ $childName }}_qty"
          min="1" value="{{ $child['qty'] }}" style="width: 60px; display: none;">
      </div>
      @endforeach
    </div>
  </div>
  @else
  @php
  $isGrayed = false;
  $tooltip = '';
  if(isset($grayOutItems[$name])) {
  $range = $grayOutItems[$name];
  $from = Carbon::parse($range['from']);
  $to = Carbon::parse($range['to']);
  $isGrayed = !$now->between($from, $to);
  $tooltip = "Verfügbar vom " . $from->format('d.m.Y') . " bis " . $to->format('d.m.Y');
  }
  @endphp
  <div class="form-check d-flex align-items-center mb-2">
    <div class="flex-grow-1">
      <input class="form-check-input item-checkbox" type="checkbox" name="{{ $name }}" data-has-quantity-input="true"
        id="{{ $name }}" @if($isGrayed) disabled style="pointer-events:none;opacity:.6;" data-toggle="tooltip"
        title="{{ $tooltip }}" @endif>
      <label class="form-check-label" for="{{ $name }}" @if($isGrayed) style="color:#888;"
        title="{{ $tooltip }}" @endif>
        {{ $data['label'] }}
        @if($isGrayed)
        <span class="ml-1" style="font-size:90%;color:#888;" data-toggle="tooltip" title="{{ $tooltip }}">
          <i class="fas fa-info-circle"></i>
        </span>
        @endif
      </label>
    </div>
    <div class="flex-shrink-0">
      <input class="form-control form-control-sm ml-3 quantity-input" type="number" name="{{ $name }}_qty" min="1"
        value="{{ $data['qty'] }}" style="width: 60px; display: none;" @if($isGrayed) disabled @endif>
    </div>
  </div>
  @endif
  @endforeach



</div>