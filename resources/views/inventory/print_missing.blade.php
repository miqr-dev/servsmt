<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LabelDrücken</title>
    <style>
      html {
        width: 54mm;
        height:17mm;
        margin: 0;
      }
        @page {
          size: auto;
          margin: 0;
          overflow: visible !important;
        }
        @media print {
               @page {
              size:  54mm 17mm rotated;
              height: 17mm;
              width: 54mm;
              /* margin: 50px; */
              }
        }
        @font-face {
            font-family: 'BrzBC_Code39_MK';
            src:  url('font/BrzBC_Code39_MK.woff2') format('woff2'),
                  url('font/BrzBC_Code39_MK.woff') format('woff'),
                  url('font/BrzBC_Code39_MK.svg#BrzBC_Code39_MK') format('svg');
                  font-weight: normal;
                  font-style: normal;
                  font-display: swap;
            }

        .print {
          min-height: 17mm;
          min-width:  54mm;
          padding: 0px;
          margin: 120px 120px 135px -70px;
          font-family: 'BrzBC_Code39_MK', 'Courier', 'monospace';
          text-align: center;
          /* line-height: 100px; */
        font-size: 47px;
          -webkit-transform: rotate(90deg);
          -moz-transform: rotate(90deg);
          -o-transform: rotate(90deg);
          -ms-transform: rotate(90deg);
          transform: rotate(90deg);

            }
        span {
            /* padding-top: 15px; */
            display: inline-block;
            vertical-align: middle;
            line-height: normal;
            white-space: nowrap
            }

    </style>
</head>
<body>
      <div class="print">
          <span>*{{$explode[0]}}-{{(int)($explode[1])}}-{{$explode[2]}}*</span>
      </div>
    </body>
</html>
