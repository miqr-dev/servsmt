<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <title>Überblick - LPZ</title>
  <!-- Import Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="font-sans text-bases">

  <!-- Header -->
  <header class="text-left mt-5 ml-5 text-sm">
    <p>Umschulung Kaufmann/-frau für Büromanagement</p>
    <p>Überblick</p>
  </header>

  <!-- Main Container -->
  <div class="container mx-auto mt-10" style="width: 100%;">
    <h1 class="text-lg font-bold mb-6 text-left">Maßnahmetitel: Umschulung Kaufmann/-frau für Büromanagement</h1>

    <!-- Content Section -->
    <div class="content mb-8">
      <table class="w-full">
        <tr>
          <td class="text-left align-top"><strong>Umfang/Zeitraum:</strong></td>
          <td class="text-center align-top">24 Monate</td>
          <td class="text-right align-top">{{ $maßnahmebeginn }} – {{ $maßnahmeende }}</td>
        </tr>
      </table>
    </div>


    <!-- Table Section -->
    <div class="table-container mt-6">
      <table class="w-full border-collapse text-base">
        <tr>
          <td class="p-3 border border-black">Theorie Lernfelder gem. Rahmenlehrplan</td>
          <td class="p-3 border border-black">880 UStd.</td>
        </tr>
        <tr>
          <td class="p-3 border border-black">Praktische Ausbildung</td>
          <td class="p-3 border border-black">{{ $stunden_praktlische_ausbildung }} Std.</td>
        </tr>
        <tr>
          <td class="p-3 border border-black">Betriebliche Ausbildung</td>
          <td class="p-3 border border-black">1008 Std.</td>
        </tr>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <footer class="fixed bottom-5 w-full text-center text-sm">
    <p>Mitteldeutsches Institut für Qualifikation und berufliche Rehabilitation &#45; MIQR GmbH</p>
  </footer>

</body>

</html>