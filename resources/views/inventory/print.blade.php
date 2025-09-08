<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LabelDrücken</title>
    <style>
        @page {
            size: 54mm 17mm rotated;
        }
        @media print {
            html, body {
                height: 80%;
            }
        }
        @font-face {
            font-family: 'BrzBC_Code39_MK';
            src: url('font/BrzBC_Code39_MK.woff2') format('woff2'),
                  url('font/BrzBC_Code39_MK.woff') format('woff'),
                  url('font/BrzBC_Code39_MK.svg#BrzBC_Code39_MK') format('svg');
                  font-weight: normal;
                  font-style: normal;
                  font-display: swap;
            }

        .print {
            height: 17mm;
            min-width:  54mm;
            border: solid black thin;
            padding: 0px;
            margin: 120px 400px 650px -120px;
            font-family: 'BrzBC_Code39_MK', 'Courier', 'monospace';
            text-align: center;
            line-height: 100px;
            font-size: 60px;
            -webkit-transform: rotate(90deg);
            -moz-transform: rotate(90deg);
            -o-transform: rotate(90deg);
            -ms-transform: rotate(90deg);
            transform: rotate(90deg);
            }
        span {
            padding-top: 15px;
            display: inline-block;
            vertical-align: middle;
            line-height: normal;
            }
    </style>
</head>
<body>
    @for($i=0; $i < $anzahl; $i++)
        <div class="print">
            <span>*{{$explode[0]}}-{{(int)($explode[1])+$i}}-{{$explode[2]}}*</span>
        </div>
    @endfor
    </body>
</html>
