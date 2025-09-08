<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anmeldung einer Umschulungsmaßnahme</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            line-height: 1.5;
        }
        .header {
            text-align: right;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 10px;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .content-table td, .content-table th {
            border: 1px solid black;
            padding: 5px;
        }
        .footer-note {
            margin-top: 30px;
            font-size: 10pt;
        }
        .footer-signature {
            margin-top: 60px;
            font-size: 10pt;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>Industrie- und Handelskammer Erfurt<br>Abteilung Aus- und Weiterbildung<br>Arnstädter Str. 34<br>99096 Erfurt</p>
        </div>

        <div class="title">
            <p>Anmeldung einer Umschulungsmaßnahme in einem anerkannten Ausbildungsberuf</p>
        </div>

        <div class="section-title">Angaben zur Umschulungsmaßnahme</div>
        <table class="content-table">
            <tr>
                <td>Ausbildungsberuf:</td>
                <td>Kaufmann/ -frau für Büromanagement</td>
            </tr>
            <tr>
                <td>Maßnahmebeginn:</td>
                <td>{{ $maßnahmebeginn }}</td>
            </tr>
            <tr>
                <td>Maßnahmeende:</td>
                <td>{{ $maßnahmeende }}</td>
            </tr>
            <tr>
                <td>Dauer in Monaten:</td>
                <td>24</td>
            </tr>
            <tr>
                <td>Anzahl Teilnehmer:</td>
                <td>15</td>
            </tr>
        </table>

        <div class="footer-note">
            <p>Die Richtlinien für Gruppenumschulungen im Bereich der IHK Erfurt haben wir zur Kenntnis genommen.</p>
            <p>Kosten: Für die Prüfung von Konzepten außerbetrieblicher Ausbildung und Umschulung erhebt die IHK Erfurt eine Gebühr in Höhe von 136,00 €.</p>
        </div>

        <div class="footer-signature">
            <p>Erfurt, 14.05.2024</p>
            <p>__________________________________</p>
            <p>Unterschrift und Stempel der Umschulungsstätte</p>
        </div>
    </div>
</body>
</html>
