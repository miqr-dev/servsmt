<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anschreiben Frau Gräbner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 50px auto;
            line-height: 1.5;
        }
        .header {
            font-size: 10pt;
            margin-bottom: 50px;
        }
        .address {
            margin-top: 30px;
            font-size: 12pt;
            line-height: 1.6;
        }
        .date {
            text-align: right;
            font-size: 12pt;
            margin: 30px 0;
        }
        .subject {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .body {
            font-size: 12pt;
            line-height: 1.6;
        }
        .closing {
            margin-top: 50px;
            font-size: 12pt;
        }
        .signature {
            margin-top: 60px;
            font-size: 12pt;
        }
        .signature-title {
            margin-top: 20px;
            font-size: 10pt;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>Mitteldeutsches Institut &#45; Heinrichstraße 89 &#45; 99092 Erfurt</p>
        </div>

        <div class="address">
            <p>Industrie- und Handelskammer Erfurt<br>
            Frau Gräbner<br>
            Arnstädter Straße 34<br>
            99096 Erfurt</p>
        </div>

        <div class="date">
            <p>Erfurt, {{ $tagesaktuellesDatum }}</p>
        </div>

        <div class="subject">
            <p>Einreichung zur Gruppenumschulung Kaufmann/-frau für Büromanagement</p>
        </div>

        <div class="body">
            <p>Sehr geehrte Frau Gräbner,</p>

            <p>hiermit übersende ich Ihnen die Anmeldeunterlagen für die Gruppenumschulung zum/-r  
            Kaufmann/-frau für Büromanagement.</p>

            <p>Ich bedanke mich vielmals im Voraus.</p>

            <p>Für Fragen stehe ich Ihnen unter der Telefonnummer 0361 511 503-319 gern zur Verfügung.</p>
        </div>

        <div class="closing">
            <p>Mit freundlichen Grüßen</p>
        </div>

        <div class="signature">
            <p>S. Wolf</p>
            <p class="signature-title">Zertifizierung / Qualitätsmanagement</p>
        </div>
    </div>
</body>
</html>
