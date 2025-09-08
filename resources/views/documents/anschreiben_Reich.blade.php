<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Anschreiben Reich</title>
    <!-- Import Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
      <style>
    .custom-footer {
      font-size: 6px;
      color: #777777;
      line-height: 1.2;
    }

    table {
      width: 100%;
      margin-top: 10px;
    }

    td {
      vertical-align: top;
      padding-right: 20px;
      white-space: nowrap;
    }

    .footer-section {
      color: #777777;
      font-size: 9px;
    }
  </style>
</head>

<body class="font-sans text-base leading-relaxed">

    <!-- Header -->
    <div class="flex justify-between items-start mt-4 mx-8">
        <div class="text-sm text-left">
            <p></p>
        </div>
        <div class="text-right">
            <img src="{{ public_path('images/admin_images/mitteldeusches_institut.png') }}"
                alt="Mitteldeutsches Institut Logo" class="w-80">
        </div>
    </div>

    <!-- Address Block -->
    <div class="mt-32 ml-12 text-sm leading-tight">
        <p class="text-gray-400" style="font-size: 7px;">Mitteldeutsches Institut &#45; Löscherstraße 16 &#45; 01309 Dresden</p>
        <p>Industrie- und Handelskammer Dresden<br>
            Geschäftsbereich Bildung<br>
            Referat Ausbildungsberatung<br>
            Mügelner Str. 40<br>
            01237 Dresden
        </p>
    </div>

    <!-- Date and Salutation -->
    <div class="mt-12 mx-12">
        <p class="text-right">Dresden, {{ $tagesaktuellesDatum }}</p>
        <p class="mt-8">Sehr geehrte Frau Reich,</p>
        <p class="mt-4">hiermit übersende ich Ihnen die Anmeldeunterlagen für die Gruppenumschulung zum Kaufmann/-frau für
            Büromanagement.</p>
        <p class="mt-4">Für Fragen stehe ich Ihnen unter der Telefonnummer 0361 / 511 503 319 gern zur Verfügung.</p>
        <p class="mt-8">Mit freundlichen Grüßen,<br><br><br><br><br><br>S. Wolf<br><span class="text-xs">Zertifizierung/Qualitätsmanagement</span></p>
    </div>

    <!-- Footer -->
  <div class="custom-footer mx-8 mt-16 pt-4 fixed bottom-8 w-full">
    <table>
      <tr class="footer-section">
        <td class="pr-6">
          <p>Mitteldeutsches Institut für Qualifikation<br>und berufliche Rehabilitation<br>MIQR GmbH<br>
            Löscherstraße 16 · 01309 Dresden
          </p>
        </td>
        <td class="pr-6">
          <p>Telefon: 0351 314 600 0<br>
            Telefax: 0351 314 600 20<br>
            Mobil: 0177 553 583 7<br>
            E-Mail: info@miqr.de
          </p>
        </td>
        <td class="pr-6">
          <p>Commerzbank<br>
            DE86 7834 0091 0850 4938 00<br>
            COBADEFFXXX
          </p>
        </td>
        <td>
          <p>Geschäftsführer: Dr. Patrick Staffel<br>
            Sitz der Gesellschaft: Erfurt<br>
            Registergericht Jena, HRB 503875<br>
            Steuernummer: 151/11408502
          </p>
        </td>
      </tr>
    </table>
  </div>

</body>

</html>
