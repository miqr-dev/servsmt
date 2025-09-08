<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LabelDrücken</title>
    <style>
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #FAFAFA;
            font: 12pt "Tahoma";
        }
        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 10mm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }
            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="book">
        <div class="page">
            <p style="text-align: center;"><span style="font-size: 20px;">Verschrottungsprotokoll</span></p>
            <p style="text-align: center;"><br></p>
            <p style="text-align: center;"><br></p>
            <p style="text-align: center;"><br></p>
            <p style="text-align: right;">Datum:{{ \Carbon\Carbon::parse($items->ausdat)->format('d-m-Y')}}</p>
            <p style="text-align: right;"><br></p>
            <p style="text-align: right;"><br></p>
            <ul>
              <li style="text-align: left; line-height: 1.5;"><strong>Inv.-Nummer:&nbsp;</strong>{{ $items->invnr }}</li>
              <li style="text-align: left; line-height: 1.5;"><strong>Geräteart:</strong> {{$items->garts->name}}</li>
              <li style="text-align: left; line-height: 1.5;"><strong>Gerätename:</strong> {{$items->gname}}</li>
              <li style="text-align: left; line-height: 1.5;"><strong>Seriennummer:&nbsp;</strong>{{$items->sn}}</li>
              <li style="text-align: left; line-height: 1.5;"><strong>Letzter Standort:</strong> {{$items->location->address}}, &nbsp;<strong>Raum</strong>: {{$room}} </li>
            </ul>
            <hr>
            <p style="margin-left: 20px;"><br></p>
            <p style="margin-left: 20px;"><strong>Grund der Ausmusterung:</strong> {{$items->amgs->name}}</p>
            <p><br></p>
            <hr>
            <p style="margin-left: 20px;"><strong>Notizen:</strong> {{ $items->notes }}</p>
            <p style="margin-left: 20px;"><br></p>
            <p style="margin-left: 20px;"><br></p>
            <p style="margin-left: 20px;"><br></p>
            <p style="margin-left: 20px;"><br></p>
            <p style="margin-left: 20px;"><br></p>
            <p style="margin-left: 20px;"><em>Unterschrift________________________</em></p>
            <p><br></p>
        </div>
    </div>
    <script>
        print();
        setTimeout("closePrintView()", 3000);
        function closePrintView() {
            document.location.href ='/inventory';
        }
    </script>
</body>
</html>
