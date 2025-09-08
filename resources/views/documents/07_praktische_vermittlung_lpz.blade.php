<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <title>Praktische Vermittlung gem. Ausbildungsrahmenplan - zeitlich</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .page-break {
      page-break-before: always;
    }

    table {
      font-family: 'Calibri', sans-serif;
      font-size: 10pt;
    }

    th,
    td {
      padding: 4px;
      vertical-align: top;
    }

    footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      text-align: center;
    }

    .container {
      margin-bottom: 50px;
    }
  </style>
</head>

<body class="font-sans text-sm leading-relaxed">

  <!-- First Page -->
  <header class="text-center mt-2">
    <p class="text-xs">Umschulung Kaufmann/-frau für Büromanagement</p>
    <p class="text-xs">Praktische Vermittlung gem. Ausbildungsrahmenplan – zeitlich</p>
  </header>

  <div class="container mx-auto my-8">
    <h1 class="text-base font-bold text-center mb-4">Sachliche und zeitliche Gliederung des Ausbildungsplanes für die
      Umschulung zum/zur Kaufmann/-frau für Büromanagement</h1>
    <p class="text-sm mb-6">Der Ausbildungsplan ist Bestandteil des Umschulungsvertrages</p>

    <div class="my-6">
      <table class="w-full">
        <tr>
          <td class="text-left"><strong>Ausbildungszeit:</strong></td>
          <td class="text-right">{{ $maßnahmebeginn }} – {{ $maßnahmeende }}</td>
        </tr>
        <tr>
          <td class="text-left"><strong>Handlungsorientierte Lerneinheiten HL WQ 1:</strong></td>
          <td class="text-right">{{ $hol1_beginn }} – {{ $hol1_ende }}</td>
        </tr>
        <tr>
          <td class="text-left"><strong>Handlungsorientierte Lerneinheiten HL WQ 2:</strong></td>
          <td class="text-right">{{ $hol2_beginn }} – {{ $hol2_ende }}</td>
        </tr>
        <tr>
          <td class="text-left"><strong>Praktikum dual:</strong></td>
          <td class="text-right">{{ $praktikumsbeginn }} – {{ $praktikumsende }}</td>
        </tr>
      </table>
    </div>

    <table class="w-full border-collapse">
      <thead>
        <tr>
          <th class="border border-black p-1 text-center">Teil A<br>(Während der gesamten Ausbildungszeit)</th>
          <th class="border border-black p-1 text-center">Praktikum</th>
          <th class="border border-black p-1 text-center">Handlungsorientierte Lerneinheiten WQ</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 1.3<br>Berufsbildung</td>
          <td class="border-l border-r border-b border-black p-1 text-center" rowspan="8"
            style="vertical-align : middle;text-align:center;">70 Stunden</td>
          <td class="border-l border-r border-b border-black p-1" style="vertical-align : middle;text-align:center;" rowspan="8"></td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 1.4<br>arbeits-, sozial-,
            mitbestimmungsrechtliche und tarif- oder beamtenrechtliche Vorschriften</td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 1.5<br>Sicherheit und Gesundheitsschutz bei der
            Arbeit</td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 1.6<br>Umweltschutz</td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 1.7<br>wirtschaftliches und nachhaltiges Denken
            und Handeln</td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 2.4<br>qualitätsorientiertes Handeln in
            Prozessen</td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 3.3<br>Kooperation und Teamarbeit</td>
        </tr>
        <tr>
          <td class="border-l border-r border-b border-black p-1">Absatz 4 Nummer 3.4<br>Anwenden einer Fremdsprache bei
            Fachaufgaben</td>
        </tr>
      </tbody>
    </table>
  </div>

  <footer class="text-center text-xs pt-5">
    <p>Mitteldeutsches Institut für Qualifikation und berufliche Rehabilitation &#45; MIQR GmbH</p>
  </footer>

  <!-- Second Page -->
  <div class="page-break"></div>

  <header class="text-center mt-2">
    <p class="text-xs">Umschulung Kaufmann/-frau für Büromanagement</p>
    <p class="text-xs">Praktische Vermittlung gem. Ausbildungsrahmenplan – zeitlich</p>
  </header>

  <div class="container mx-auto my-2">
    <table class="w-full border-collapse">
      <thead>
        <tr>
          <th class="border border-black p-1 text-center">Teil B</th>
          <th class="border border-black p-1 text-center">Praktikum</th>
          <th class="border border-black p-1 text-center">hoL WQ</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 1.1<br>Informationsmanagement</td>
          <td class="border-l border-r border-b border-black p-1 text-center" style="vertical-align: middle; text-align: center;" rowspan="16">266 Stunden</td>
          <td class="border-l border-r border-b border-black p-1" style="vertical-align: middle; text-align: center;"
            rowspan="16"></td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 1.2<br>Informationsverarbeitung</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 1.1<br>Stellung, Rechtsform und
            Organisationsstruktur</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 2.3<br>Datenschutz und Datensicherheit</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 3.1<br>Informationsbeschaffung und Umgang mit
            Informationen</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 1.3<br>bürowirtschaftliche Abläufe</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 1.4<br>Koordinations- und Organisationsaufgaben
          </td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 2.1<br>Arbeits- und Selbstorganisation,
            Organisationsmittel</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 2.2<br>Arbeitsplatzergonomie</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 2.3<br>Beschaffung von Material und externen
            Dienstleistungen</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 4 Nummer 1.2<br>Produkt- und Dienstleistungsangebot</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 2.1<br>Kundenbeziehungsprozesse</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 2.2<br>Auftragsbearbeitung und -nachbereitung
          </td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 2.4<br>personalbezogene Aufgaben</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 2 Nummer 2.5<br>kaufmännische Steuerung</td>

        </tr>
        <tr>
          <td class="border-l border-r border-b border-black p-1">Absatz 4 Nummer 3.2<br>Kommunikation</td>
        </tr>
      </tbody>
    </table>
  </div>

  <footer class="text-center text-xs pt-5">
    <p>Mitteldeutsches Institut für Qualifikation und berufliche Rehabilitation &#45; MIQR GmbH</p>
  </footer>

  <!-- Third Page -->
  <div class="page-break"></div>

  <header class="text-center mt-2">
    <p class="text-xs">Umschulung Kaufmann/-frau für Büromanagement</p>
    <p class="text-xs">Praktische Vermittlung gem. Ausbildungsrahmenplan – zeitlich</p>
  </header>

  <div class="container mx-auto my-2">
    <table class="w-full border-collapse">
      <thead>
        <tr>
          <th class="border border-black p-1 text-center">Teil C</th>
          <th class="border border-black p-1 text-center">Praktikum</th>
          <th class="border border-black p-1 text-center">Handlungsorientierte Lerneinheiten WQ</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 3 Nummer 1<br>Auftragssteuerung und -koordination</td>
          <td class="border-l border-r border-b border-black p-1" style="vertical-align: middle; text-align: center;"
            rowspan="8"></td>
          <td class="border-l border-r border-b border-black p-1 text-center" style="vertical-align: middle; text-align: center;" rowspan="8">Pro Wahlqualifikation 336
            Stunden</td>
        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 3 Nummer 2<br>kaufmännische Steuerung und Kontrolle</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 3 Nummer 3<br>kaufmännische Abläufe in kleinen und
            mittleren Unternehmen</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 3 Nummer 4<br>Einkauf und Logistik</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 3 Nummer 5<br>Marketing und Vertrieb</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 3 Nummer 6<br>Personalwirtschaft</td>

        </tr>
        <tr>
          <td class="border-l border-r border-black p-1">Absatz 3 Nummer 7<br>Assistenz und Sekretariat</td>

        </tr>
        <tr>
          <td class="border-l border-r border-b border-black p-1">Absatz 3 Nummer 8<br>Öffentlichkeitsarbeit und
            Veranstaltungsmanagement</td>
        </tr>
      </tbody>
    </table>
  </div>

  <footer class="text-center text-xs pt-5">
    <p>Mitteldeutsches Institut für Qualifikation und berufliche Rehabilitation &#45; MIQR GmbH</p>
  </footer>

</body>

</html>