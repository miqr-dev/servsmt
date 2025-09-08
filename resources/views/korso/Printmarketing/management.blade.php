@extends('layouts.admin_layout.admin_layout')

<style>
  .strike {
    text-decoration: line-through;
  }

  /* flash from yellow → transparent in 2s */
  @keyframes flashHighlight {
    from {
      background-color: #fffb91;
    }

    to {
      background-color: transparent;
    }
  }

  .highlighted {
    animation: flashHighlight 2s ease-in-out;
  }
</style>

@section('content')
<div class="container-fluid mt-4">
  @php
  Log::debug('Rendering Printmarketing management view', [
  'summary_count' => count($summary),
  'details_count' => count($details)
  ]);

  // 1) Explicit key lists for each category
  $flyerKeys = [
  'AfA_Kompakt',
  'Arbeitgeberflyer_Umschulung',
  'Arbeitgeberflyer_Weiterbildung',
  'DRV_Kompakt',
  'Aktualisierung_APO',
  'Aktualisierung_PAA',
  'Aktualisierung_BUS',
  'Aktualisierung_ISO',
  'Aktualisierung_ISO_Kompakt',
  'Aktualisierung_IBO',
  'Aktualisierung_MWe_Kompakt',
  'Aktualisierung_Bewerbungstraining',
  'Aktualisierung_kbQ',
  'Aktualisierung_Umschulung_Kompakt',
  'Aktualisierung_KBM',
  'Aktualisierung_IK',
  'Aktualisierung_KiG',
  'Aktualisierung_K_ECom',
  'DRV_FOSI',
  'DRV_OSI',
  'DRV_BT_S',
  'DRV_VL_bbU',
  'DRV_bbU',
  'DRV_RVL',
  'DRV_RVL_intensiv',
  'DRV_Umschulung_Kompakt',
  'DRV_KBM',
  'DRV_IK',
  'DRV_KIG',
  'DRV_K_ECOM',
  'Berufssprachkurse_BAMF-DE_EN',
  'Berufssprachkurse_BAMF-DE_UA',
  'Berufssprachkurse_BAMF-DE_AR',
  'Berufssprachkurse_BAMF-DE_ES',
  'BAMF_Deutsch_DE_EN',
  'BAMF_Deutsch_DE_UA',
  'BAMF_Deutsch_DE_AR',
  'BAMF_Deutsch_DE_ES',
  'BAMF_Alpha_DE_EN',
  'BAMF_Alpha_DE_UA',
  'BAMF_Alpha_DE_AR',
  'BAMF_Alpha_DE_ES',
  'BAMF_Zweit_DE_EN',
  'BAMF_Zweit_DE_UA',
  'BAMF_Zweit_DE_AR',
  'BAMF_Zweit_DE_ES',
  'BAMF_Gering_DE_EN',
  'BAMF_Gering_DE_UA',
  'BAMF_Gering_DE_AR',
  'BAMF_Gering_DE_ES',
  'MAPO_DE_EN',
  'MAPO_DE_UA',
  'MISO_DE_EN',
  'MISO_DE_UA',
  'MISO_MISO-K mit JobBSK',
  'MIBO_DE_EN',
  'MIBO_DE_UA',
  'Willkommensmappe',
  'Willkommensmappe_freie_MA',
  'Organigramm',
  'Erstellung_Anzeige',
  'Vorlage_Schuelerausweise',
  'Vorlage_Namensschilder',
  'Vorlage_Ansprechpartner'
  ];

  $giveawayKeys = [
  'City_Cards_Do_you_speak_German','City_Cards_Salad','City_Cards_Sauerkraut','City_Cards_Smiley',
  'City_Cards_Egg','Tragetaschen','Einkaufswagenloeser','Pflastermaeppchen',
  'Fruchtgummi','Kugelschreiber'
  ];

  $stationeryKeys = [
  'Visitenkarten',
  'Glueckwunschkarte_Alles_Gute',
  'Glueckwunschkarte_blanco',
  'GA_Mappen',
  'Zeugnismappe',
  'Zeugnismappe_Deutschkurse',
  'Zeugnispapier',
  'A5_Ringblock',
  'A4_Schreibblock_Streifen',
  // 'A4_Schreibblock_Sprache',
  'USB_Stick',
  'Notizblock_PostIt_Stift',
  // --- Expanded Versandtasche items ---
  'Versandtasche_Fenster_C4',
  'Versandtasche_Fenster_C5',
  'Versandtasche_Fenster_DL',
  'Versandtasche_ohne_Fenster_C4',
  'Versandtasche_ohne_Fenster_C5',
  'Versandtasche_ohne_Fenster_DL',
  // ---
  'Block_A6',
  ];

  $signageKeys = ['Beklebung','Beschilderung','Plakate'];

  $messeKeys = [
  'Anmeldung_Messe','Ausstattung_Messe','Rollup_Anzahl','Plakate_Anzahl',
  'Messestand','Beach_Flag','Sonstiges'
  ];

  // 2) Group once and slice
  $grouped = $summary->groupBy('item_name');
  $flyerGroups = $grouped->only($flyerKeys);
  $giveawayGroups = $grouped->only($giveawayKeys);
  $stationeryGroups = $grouped->only($stationeryKeys);
  $signageGroups = $grouped->only($signageKeys);
  $messeGroups = $grouped->only($messeKeys);
  @endphp

  @php
  // Grouping logic for Versandtasche items
  $versandtascheMap = [
  'Versandtasche mit Fenster' => [
  'Versandtasche_Fenster_C4' => 'C4',
  'Versandtasche_Fenster_C5' => 'C5',
  'Versandtasche_Fenster_DL' => 'DL',
  ],
  'Versandtasche ohne Fenster' => [
  'Versandtasche_ohne_Fenster_C4' => 'C4',
  'Versandtasche_ohne_Fenster_C5' => 'C5',
  'Versandtasche_ohne_Fenster_DL' => 'DL',
  ],
  ];

  // Build new display array
  $stationeryDisplay = [];
  $addedKeys = [];

  foreach ($versandtascheMap as $groupLabel => $typeMap) {
  $children = [];
  foreach ($typeMap as $key => $label) {
  if (isset($stationeryGroups[$key])) {
  $children[$key] = [
  'label' => $label,
  'rows' => $stationeryGroups[$key],
  ];
  $addedKeys[] = $key;
  }
  }
  if (count($children)) {
  $stationeryDisplay[] = [
  'group' => $groupLabel,
  'children' => $children
  ];
  }
  }

  // Add all other items as normal
  foreach ($stationeryGroups as $itemName => $rows) {
  if (!in_array($itemName, $addedKeys)) {
  $stationeryDisplay[] = [
  'single' => [
  'name' => $itemName,
  'rows' => $rows
  ]
  ];
  }
  }
  @endphp


  <h1><strong>Printmarketing Verwaltung</strong></h1>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('korso.dashboard') }}" class="btn btn-success">
      Zurück
    </a>
    <div>
      <button id="btn-pdf-active" class="btn btn-primary">
        PDF (Aktive Ansicht)
      </button>
      <a href="{{ route('printmarketing.pdf', ['tab' => 'all']) }}" target="_blank" class="btn btn-primary">
        PDF (Alle Kategorien)
      </a>
    </div>
  </div>

  {{-- Primary tabs --}}
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-toggle="tab" href="#summary">Summary</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#detailed">Detailed</a>
    </li>
  </ul>

  <div class="tab-content pt-3">
    {{-- SUMMARY with sub-tabs --}}
    <div class="tab-pane fade show active" id="summary" role="tabpanel">
      {{-- Sub-tabs --}}
      <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#flyer">Flyer / Infomaterial</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#giveaways">Give Aways</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#stationery">Geschäftsausstattung</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#signage">Beschilderung / Gestaltung</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#messe">Messe &amp; Sonstiges</a></li>
      </ul>

      <div class="tab-content">
        {{-- Flyer pane --}}
        <div class="tab-pane fade show active" id="flyer" role="tabpanel">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Artikel</th>
                <th>Standort</th>
                <th>Gesamt Menge</th>
              </tr>
            </thead>
            <tbody>
              @foreach($flyerGroups as $itemName => $rows)
              @php
              $rawTotal = $rows->sum('total');
              $alreadyDone = $details
              ->where('item_name', $itemName)
              ->where('ordered', true)
              ->sum('quantity');
              $remaining = max(0, $rawTotal - $alreadyDone);
              @endphp
              <tr>
                <td>
                  <label class="mb-0">
                    <input type="checkbox" class="select-all" data-item-name="{{ $itemName }}"
                      style="margin-right: .5em;">
                    {{ $itemName }}
                  </label>
                </td>
                <td>
                  @foreach($rows as $r)
                  @php
                  $loc = $r->location_name;
                  $tot = $r->total;
                  $doneHere = $details
                  ->filter(function($d) use($itemName, $loc) {
                  return $d->item_name === $itemName
                  && $d->ordered
                  && $d->korso->location->address === $loc;
                  })
                  ->isNotEmpty();
                  @endphp
                  <span class="location-group {{ $doneHere ? 'strike' : '' }}" data-item-name="{{ $itemName }}"
                    data-location="{{ $loc }}">
                    {{ $loc }}:
                    <a href="#" class="jump-detail" data-item-name="{{ $itemName }}" data-location="{{ $loc }}">
                      <strong class="text-primary">{{ $tot }}</strong>
                    </a>
                  </span>
                  @if(! $loop->last) | @endif
                  @endforeach
                </td>
                <td>
                  <span class="remaining-count" data-item-name="{{ $itemName }}">
                    {{ $remaining }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>


        {{-- Giveaways pane --}}
        <div class="tab-pane fade" id="giveaways" role="tabpanel">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Artikel</th>
                <th>Standort</th>
                <th>Gesamt Menge</th>
              </tr>
            </thead>
            <tbody>
              @foreach($giveawayGroups as $itemName => $rows)
              @php
              // 1) raw requested total for this item
              $rawTotal = $rows->sum('total');
              // 2) how many have already been ticked off
              $alreadyDone = $details
              ->where('item_name', $itemName)
              ->where('ordered', true)
              ->sum('quantity');
              // 3) remaining stock
              $remaining = max(0, $rawTotal - $alreadyDone);
              @endphp
              <tr>
                <td>
                  <label class="mb-0">
                    <input type="checkbox" class="select-all" data-item-name="{{ $itemName }}"
                      style="margin-right: .5em;">
                    {{ $itemName }}
                  </label>
                </td>

                <td>
                  @foreach($rows as $r)
                  @php
                  $loc = $r->location_name;
                  $tot = $r->total;
                  $doneHere = $details
                  ->filter(function($d) use($itemName, $loc) {
                  return $d->item_name === $itemName
                  && $d->ordered
                  && $d->korso->location->address === $loc;
                  })
                  ->isNotEmpty();
                  @endphp
                  <span class="location-group {{ $doneHere ? 'strike' : '' }}" data-item-name="{{ $itemName }}"
                    data-location="{{ $loc }}">
                    {{ $loc }}:
                    <a href="#" class="jump-detail" data-item-name="{{ $itemName }}" data-location="{{ $loc }}">
                      <strong class="text-primary">{{ $tot }}</strong>
                    </a>
                  </span>
                  @if (! $loop->last) | @endif
                  @endforeach
                </td>
                <td>
                  <span class="remaining-count" data-item-name="{{ $itemName }}">
                    {{ $remaining }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Stationery pane --}}
        <div class="tab-pane fade" id="stationery" role="tabpanel">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Artikel</th>
                <th>Standort</th>
                <th>Gesamt Menge</th>
              </tr>
            </thead>
            <tbody>
              @foreach($stationeryDisplay as $entry)
              @if(isset($entry['group']))
              <tr>
                <td colspan="3"><strong>{{ $entry['group'] }}</strong></td>
              </tr>
              @foreach($entry['children'] as $typeKey => $child)
              @php
              $rawTotal = $child['rows']->sum('total');
              $alreadyDone = $details->where('item_name', $typeKey)->where('ordered', true)->sum('quantity');
              $remaining = max(0, $rawTotal - $alreadyDone);
              @endphp
              <tr>
                <td class="pl-4">
                  <label class="mb-0">
                    <input type="checkbox" class="select-all" data-item-name="{{ $typeKey }}"
                      style="margin-right: .5em;">
                    {{ $child['label'] }}
                  </label>
                </td>
                <td>
                  @foreach($child['rows'] as $r)
                  @php
                  $loc = $r->location_name;
                  $tot = $r->total;
                  $doneHere = $details
                  ->filter(function($d) use($typeKey, $loc) {
                  return $d->item_name === $typeKey && $d->ordered && $d->korso->location->address === $loc;
                  })
                  ->isNotEmpty();
                  @endphp
                  <span class="location-group {{ $doneHere ? 'strike' : '' }}" data-item-name="{{ $typeKey }}"
                    data-location="{{ $loc }}">
                    {{ $loc }}:
                    <a href="#" class="jump-detail" data-item-name="{{ $typeKey }}" data-location="{{ $loc }}">
                      <strong class="text-primary">{{ $tot }}</strong>
                    </a>
                  </span>
                  @if(! $loop->last) | @endif
                  @endforeach
                </td>
                <td>
                  <span class="remaining-count" data-item-name="{{ $typeKey }}">
                    {{ $remaining }}
                  </span>
                </td>
              </tr>
              @endforeach
              @elseif(isset($entry['single']))
              @php
              $itemName = $entry['single']['name'];
              $rows = $entry['single']['rows'];
              $rawTotal = $rows->sum('total');
              $alreadyDone = $details->where('item_name', $itemName)->where('ordered', true)->sum('quantity');
              $remaining = max(0, $rawTotal - $alreadyDone);
              @endphp
              <tr>
                <td>
                  <label class="mb-0">
                    <input type="checkbox" class="select-all" data-item-name="{{ $itemName }}"
                      style="margin-right: .5em;">
                    {{ $itemName }}
                  </label>
                </td>
                <td>
                  @foreach($rows as $r)
                  @php
                  $loc = $r->location_name;
                  $tot = $r->total;
                  $doneHere = $details
                  ->filter(function($d) use($itemName, $loc) {
                  return $d->item_name === $itemName && $d->ordered && $d->korso->location->address === $loc;
                  })
                  ->isNotEmpty();
                  @endphp
                  <span class="location-group {{ $doneHere ? 'strike' : '' }}" data-item-name="{{ $itemName }}"
                    data-location="{{ $loc }}">
                    {{ $loc }}:
                    <a href="#" class="jump-detail" data-item-name="{{ $itemName }}" data-location="{{ $loc }}">
                      <strong class="text-primary">{{ $tot }}</strong>
                    </a>
                  </span>
                  @if(! $loop->last) | @endif
                  @endforeach
                </td>
                <td>
                  <span class="remaining-count" data-item-name="{{ $itemName }}">
                    {{ $remaining }}
                  </span>
                </td>
              </tr>
              @endif
              @endforeach
            </tbody>
          </table>
        </div>


        {{-- Signage pane --}}
        <div class="tab-pane fade" id="signage" role="tabpanel">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Artikel</th>
                <th>Standort</th>
                <th>Gesamt Menge</th>
              </tr>
            </thead>
            <tbody>
              @foreach($signageGroups as $itemName => $rows)
              @php
              // 1) raw requested total
              $rawTotal = $rows->sum('total');
              // 2) how many already ordered
              $alreadyDone = $details
              ->where('item_name', $itemName)
              ->where('ordered', true)
              ->sum('quantity');
              // 3) remaining stock
              $remaining = max(0, $rawTotal - $alreadyDone);
              @endphp
              <tr>
                <td>
                  <label class="mb-0">
                    <input type="checkbox" class="select-all" data-item-name="{{ $itemName }}"
                      style="margin-right: .5em;">
                    {{ $itemName }}
                  </label>
                </td>
                <td>
                  @foreach($rows as $r)
                  @php
                  $loc = $r->location_name;
                  $tot = $r->total;
                  $doneHere = $details
                  ->filter(function($d) use($itemName, $loc) {
                  return $d->item_name === $itemName
                  && $d->ordered
                  && $d->korso->location->address === $loc;
                  })
                  ->isNotEmpty();
                  @endphp
                  <span class="location-group {{ $doneHere ? 'strike' : '' }}" data-item-name="{{ $itemName }}"
                    data-location="{{ $loc }}">
                    {{ $loc }}:
                    <a href="#" class="jump-detail" data-item-name="{{ $itemName }}" data-location="{{ $loc }}">
                      <strong class="text-primary">{{ $tot }}</strong>
                    </a>
                  </span>
                  @if (! $loop->last) | @endif
                  @endforeach
                </td>
                <td>
                  <span class="remaining-count" data-item-name="{{ $itemName }}">
                    {{ $remaining }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>


        {{-- Messe pane --}}
        <div class="tab-pane fade" id="messe" role="tabpanel">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Artikel</th>
                <th>Standort</th>
                <th>Gesamt Menge</th>
              </tr>
            </thead>
            <tbody>
              @foreach($messeGroups as $itemName => $rows)
              @php
              // 1) raw requested total
              $rawTotal = $rows->sum('total');
              // 2) how many already ordered
              $alreadyDone = $details
              ->where('item_name', $itemName)
              ->where('ordered', true)
              ->sum('quantity');
              // 3) remaining stock
              $remaining = max(0, $rawTotal - $alreadyDone);
              @endphp
              <tr>
                <td>
                  <label class="mb-0">
                    <input type="checkbox" class="select-all" data-item-name="{{ $itemName }}"
                      style="margin-right: .5em;">
                    {{ $itemName }}
                  </label>
                </td>
                <td>
                  @foreach($rows as $r)
                  @php
                  $loc = e($r->location_name);
                  $tot = e($r->total);
                  @endphp
                  <span class="location-group" data-item-name="{{ $itemName }}" data-location="{{ $loc }}">
                    {{ $loc }}:
                    <a href="#" class="jump-detail" data-item-name="{{ $itemName }}" data-location="{{ $loc }}">
                      <strong class="text-primary">{{ $tot }}</strong>
                    </a>
                  </span>
                  @if (! $loop->last) | @endif
                  @endforeach
                </td>
                <td>
                  <span class="remaining-count" data-item-name="{{ $itemName }}">
                    {{ $remaining }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>


      </div>
    </div>

    {{-- DETAILED --}}
    <div class="tab-pane fade" id="detailed" role="tabpanel">
      <h4>Detailliste</h4>
      <table class="table table-striped" id="printmarketingTable">
        <thead>
          <tr>
            <th>Ticket ID</th>
            <th>Artikel</th>
            <th>Menge</th>
            <th>Standort</th>
            <th>Schon bestellt?</th>
          </tr>
        </thead>
        <tbody>
          @foreach($details as $item)
          <tr id="item-{{ $item->id }}" class="{{ $item->ordered?'text-decoration-line-through':'' }}">
            <td>{{ $item->korso->id }}</td>
            <td> <a href="{{ route('korso.show', $item->korso->id) }}" target="_blank" title="Ticket öffnen">
                {{ $item->item_name }}
              </a></td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->korso->location->address }}</td>
            <td>
              <input type="checkbox" class="toggle-ordered" data-id="{{ $item->id }}"
                data-item-name="{{ $item->item_name }}" data-location="{{ $item->korso->location->address }}"
                data-qty="{{ $item->quantity }}" {{ $item->ordered?'checked':'' }}
              >
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  $(document).ready(function () {
    // 1) Toggle ordered & update both detail & summary
    $(document).on('change', '.toggle-ordered', function () {
      const $cb = $(this),
        id = $cb.data('id'),
        name = $cb.data('item-name'),
        loc = $cb.data('location'),
        qty = parseInt($cb.data('qty'), 10);

      $.post("{{ url('korso-item') }}/" + id + "/toggle-ordered", {
        _token: "{{ csrf_token() }}"
      }, function (data) {
        // a) strike detail row
        $('#item-' + id)
          .toggleClass('strike', data.ordered);

        // b) update remaining-count
        const $span = $('.remaining-count')
          .filter(`[data-item-name="${name}"]`);
        let current = parseInt($span.text(), 10) || 0;
        let updated = data.ordered ? current - qty : current + qty;
        $span.text(Math.max(0, updated));

        // c) strike the exact summary span
        const sel =
          'span.location-group' +
          `[data-item-name="${name}"]` +
          `[data-location="${loc}"]`;
        $(sel).toggleClass('strike', data.ordered);
      });
    });

    // 2) Jump & highlight in detailed
    $(document).on('click', '.jump-detail', function (e) {
      e.preventDefault();
      const name = $(this).data('item-name'),
        loc = $(this).data('location');

      // Bind first, then show
      $('.nav-tabs a[href="#detailed"]')
        .one('shown.bs.tab', function () {
          const $row = $('#printmarketingTable tbody tr').filter(function () {
            return $(this).find('td:eq(1)').text().trim() === name
              && $(this).find('td:eq(3)').text().trim() === loc;
          }).first();
          if (!$row.length) return;
          $row.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
          $row.addClass('table-primary');
          setTimeout(() => $row.removeClass('table-primary'), 2000);
        })
        .tab('show');
    });

    // 3) “Select all” in summary toggles every detailed checkbox for that item
    $(document).on('change', '.select-all', function () {
      const name = $(this).data('item-name');
      const checkAll = this.checked;

      // find every detailed checkbox for that item
      $(`.toggle-ordered[data-item-name="${name}"]`).each(function () {
        // only trigger if its state actually needs to change
        if ($(this).prop('checked') !== checkAll) {
          $(this)
            .prop('checked', checkAll)
            .trigger('change');  // re-use your existing toggle handler
        }
      });
    });

    // 4) Keep summary checkbox in sync if user manually toggles one detail
    $(document).on('change', '.toggle-ordered', function () {
      const name = $(this).data('item-name');
      const $all = $(`.toggle-ordered[data-item-name="${name}"]`);
      const $checked = $all.filter(':checked');

      // if *all* detailed boxes are checked, check the summary box;
      // otherwise uncheck it.
      $(`.select-all[data-item-name="${name}"]`)
        .prop('checked', $all.length === $checked.length);
    });
  });

  $('#btn-pdf-active').on('click', function () {
    // grab the ID of the active summary tab 
    const activeHref = $('.nav-pills .nav-link.active').attr('href') || '';
    const tab = activeHref.replace('#', '') || 'all';
    window.open(
      "{{ route('printmarketing.pdf') }}" + '?tab=' + encodeURIComponent(tab),
      '_blank'
    );
  });

</script>
@endsection