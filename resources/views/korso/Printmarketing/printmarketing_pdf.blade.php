<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    h1 { text-align: center; margin-bottom: 20px; }
    h2 { margin-top: 30px; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    th, td { border: 1px solid #333; padding: 4px; }
    th { background: #eee; }
    del { color: #555; }
  </style>
</head>
<body>
  <h1>Printmarketing – Zusammenfassung</h1>

  @php
    $categories = [
      'flyer'      => ['title'=>'Flyer / Infomaterial',    'keys'=>$flyerKeys],
      'giveaways'  => ['title'=>'Give Aways',               'keys'=>$giveawayKeys],
      'stationery' => ['title'=>'Geschäftsausstattung',     'keys'=>$stationeryKeys],
      'signage'    => ['title'=>'Beschilderung / Gestaltung','keys'=>$signageKeys],
      'messe'      => ['title'=>'Messe & Sonstiges',        'keys'=>$messeKeys],
    ];
  @endphp

  @foreach($categories as $key => $cat)
    @if($tab==='all' || $tab===$key)
      <h2>{{ $cat['title'] }}</h2>
      <table>
        <thead>
          <tr>
            <th>Artikel</th>
            <th>Standort</th>
            <th>Gesamt Menge</th>
          </tr>
        </thead>
        <tbody>
        @if($key === 'stationery')
          @php
            // Grouping logic for PDF
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
            // Prepare stationery items for grouped display
            $stationeryItems = collect($summary->whereIn('item_name', $cat['keys']));
            $addedKeys = [];
          @endphp

          {{-- Versandtasche groups --}}
          @foreach($versandtascheMap as $groupTitle => $childMap)
            @php
              $hasAny = false;
              foreach ($childMap as $k => $label) {
                if ($stationeryItems->where('item_name', $k)->count()) $hasAny = true;
              }
            @endphp
            @if($hasAny)
              <tr>
                <td colspan="3"><strong>{{ $groupTitle }}</strong></td>
              </tr>
              @foreach($childMap as $k => $label)
                @foreach($stationeryItems->where('item_name', $k) as $row)
                  @php $isDone = isset($doneMap[$row->item_name][$row->location_name]); @endphp
                  <tr>
                    <td style="padding-left: 2em;">
                      @if($isDone) <del>{{ $label }}</del>
                      @else        {{ $label }}
                      @endif
                    </td>
                    <td>
                      @if($isDone) <del>{{ $row->location_name }}</del>
                      @else        {{ $row->location_name }}
                      @endif
                    </td>
                    <td>
                      @if($isDone) <del>{{ $row->total }}</del>
                      @else        {{ $row->total }}
                      @endif
                    </td>
                  </tr>
                  @php $addedKeys[] = $k; @endphp
                @endforeach
              @endforeach
            @endif
          @endforeach

          {{-- All other stationery items --}}
          @foreach($stationeryItems as $row)
            @if(!in_array($row->item_name, $addedKeys))
              @php $isDone = isset($doneMap[$row->item_name][$row->location_name]); @endphp
              <tr>
                <td>
                  @if($isDone) <del>{{ $row->item_name }}</del>
                  @else        {{ $row->item_name }}
                  @endif
                </td>
                <td>
                  @if($isDone) <del>{{ $row->location_name }}</del>
                  @else        {{ $row->location_name }}
                  @endif
                </td>
                <td>
                  @if($isDone) <del>{{ $row->total }}</del>
                  @else        {{ $row->total }}
                  @endif
                </td>
              </tr>
            @endif
          @endforeach

        @else
          {{-- all other categories --}}
          @foreach($summary->whereIn('item_name', $cat['keys']) as $row)
            @php $isDone = isset($doneMap[$row->item_name][$row->location_name]); @endphp
            <tr>
              <td>
                @if($isDone) <del>{{ $row->item_name }}</del>
                @else        {{ $row->item_name }}
                @endif
              </td>
              <td>
                @if($isDone) <del>{{ $row->location_name }}</del>
                @else        {{ $row->location_name }}
                @endif
              </td>
              <td>
                @if($isDone) <del>{{ $row->total }}</del>
                @else        {{ $row->total }}
                @endif
              </td>
            </tr>
          @endforeach
        @endif
        </tbody>
      </table>
    @endif
  @endforeach

</body>
</html>
