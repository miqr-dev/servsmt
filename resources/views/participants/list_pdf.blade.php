{{-- Teilnehmer Liste PDF export (ParticipantTicketTableController@export, type "pdf").
     Same content as the old DataTables pdf button: title + Vorname / Nachname /
     Benutzername / Passwort / Maßnahme for every row matching the current search. --}}
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>{{ $title }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
    h1 { font-size: 15px; text-align: center; margin: 0 0 10px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #661421; color: #fff; text-align: left; padding: 4px; }
    td { padding: 4px; border-bottom: 1px solid #ddd; }
    tr:nth-child(even) td { background: #f5f5f5; }
    .mono { font-family: DejaVu Sans Mono, monospace; }
  </style>
</head>
<body>
  <h1>{{ $title }}</h1>
  <table>
    <thead>
      <tr><th>Vorname</th><th>Nachname</th><th>Benutzername</th><th>Passwort</th><th>Maßnahme</th></tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
      <tr>
        <td>{{ $r['vorname'] }}</td>
        <td>{{ $r['nachname'] }}</td>
        <td><strong>{{ $r['username'] }}</strong></td>
        <td class="mono">{{ $r['password'] }}</td>
        <td>{{ $r['course'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
