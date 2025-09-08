<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Inventarergebnis</title>
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
						table tr th, table tr td {
							width: 33.3333%;
							vertical-align: middle;
							text-align: center;
							border: 1px solid black;
						}

        }
    </style>
</head>
<body>
	<div class="book">
		<div class="page">
			<p><strong><span style="font-size: 24px;">Inventur-Ergebnis-Liste:</span></strong></p>
			<hr><br> 
      <p><strong>address&nbsp;</strong><span class="text-secondary">.....................................................,</span></p>
      <p><strong>Raum</strong><span class="text-secondary">.........................................................,</span></p>
			<p style="text-align: right;">Datum:<strong>&nbsp;{{ date('d-m-Y H:i') }}</strong></p>
			<p style="text-align: left;"><br></p>
			<p style="text-align: left;"><strong>Inventar-Soll/Ist:</strong></p>
			<table style="width: 100%; border-collapse: collapse;">
				<thead>
					<tr>
						<th>Inventarnummer:</th>
						<th>Ger&auml;tenname:</th>
						<th>Vorhanden:</th>
					</tr>
				</thead>
				<tbody>
						@foreach($val as $item)
						@if (is_null($item['zuordnen']))
						<tr>
							<td>{{$item['invnr']}}</td>
							<td>{{$item['gname']}}</td>
							<td>OK</td>
						</tr>
						@endif
						@endforeach
				</tbody>
			</table>
			<p style="text-align: left;"><br></p>
			<p style="text-align: left;"><strong>Neues Inventar:</strong></p>
			<table style="width: 100%; border-collapse: collapse;">
				<thead>
					<tr>
						<th>Inventarnummer:</th>
						<th>Ger&auml;tenname:</th>
						<th>Bisheriger Standort:</th>
					</tr>
				</thead>
				<tbody>
					@foreach($val as $item)
					@if ($item['zuordnen'] == '1')
					<tr>
						<td>{{$item['invnr']}}</td>
						<td>{{$item['gname']}}</td>
						<td>{{$item['room_id_old']}}, &nbsp;{{$item['address']}} </td>
					</tr>
					@endif
					@endforeach
				</tbody>
			</table>
			<p style="text-align: left;"><br></p>
			<p style="text-align: left;"><strong>Fehlendes Inventar:</strong></p>
			<table style="width: 100%; border-collapse: collapse;">
				<thead>
					<tr>
						<th style="width: 23.8928%;">Inventarnummer:</th>
						<th style="width: 25.4079%;">Ger&auml;tenname:</th>
						<th style="width: 50.3497%;">Abgang (Grund)</th>
					</tr>
				</thead>
				<tbody>
					@foreach($val as $item)
					@if ($item['zuordnen'] == '0')
					<tr>
						<td style="width: 23.8928%;">{{$item['invnr']}}</td>
						<td style="width: 25.4079%;">{{$item['gname']}}</td>
						<td style="width: 50.3497%;"></td>
					</tr>
					@endif
					@endforeach
				</tbody>
			</table>
			<br>
			<br>
			<br>
			<br>
			<p style="text-align: left;">Unterschrift</p>
			<p style="text-align: left;"><br></p>
			<hr>
			<p><br></p>
			<p style="text-align: left;"><br></p>
								
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


