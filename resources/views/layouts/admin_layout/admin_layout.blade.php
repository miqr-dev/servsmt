<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MIQR | SMT</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="{{ url ('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
  <!-- Dropzone -->
  <link rel="stylesheet" href="{{ url('dropzone-5.7.0/dist/min/dropzone.min.css') }}" />
  <!-- FilePond -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/filepond/4.30.3/filepond.css"
    integrity="sha512-lA1v3OiAORI4FvglHuwPns240yxZFQiirFBS+93lmHG9v8qzAjHhlC69Ba/B/GlJKIfkBbp2NzfaQM25t1vVKg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- iCheck -->
  <link rel="stylesheet" href="{{ url ('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Select 2 Bootstrap Theme -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
    integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw=="
    crossorigin="anonymous" />
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ url ('css/admin_css/adminlte.min.css') }}">
  <!-- app.css -->
  <link rel="stylesheet" href="{{mix('css/app.css')}}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{ url ('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{ url ('plugins/daterangepicker/daterangepicker.css') }}">
  <!-- summernote -->
  <link rel="stylesheet" href="{{ url ('plugins/summernote/summernote-bs4.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <!-- Datatables Css -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
  <link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.dataTables.min.css">
  <link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
  <!-- toastr -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

  <link rel="stylesheet"
    href="http://demo.hackandphp.com/address-book-with-bootstrap-and-jquery/css/animatecss/animate.min.css">
  <link rel="stylesheet"
    href="http://demo.hackandphp.com/address-book-with-bootstrap-and-jquery/js/slidernav/slidernav.css">
  <!-- video.js -->
  <link href="https://vjs.zencdn.net/7.15.4/video-js.css" rel="stylesheet" />
  <link href="https://unpkg.com/@videojs/themes@1/dist/city/index.css" rel="stylesheet" />
  <!-- custom.css -->
  <link rel="stylesheet" href="{{url ('css/custom.css')}}">

  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
  <!-- bootstraptable.css -->
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.19.1/dist/bootstrap-table.min.css">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap">

  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    @include('layouts.admin_layout.admin_header')

    @include('layouts.admin_layout.admin_sidebar')
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <!-- <h1 class="m-0 text-dark">Dashboard</h1> -->
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <!-- <li class="breadcrumb-item"><a href="#">Home</a></li> -->
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      @yield('content')
    </div>

    @include('layouts.admin_layout.admin_footer')
    <!-- jQuery -->
    <script src="{{ url ('plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ url ('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
    <script>
      $.widget.bridge('uibutton', $.ui.button)
    </script>
    {{-- jQuery Cookie --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"
      integrity="sha512-3j3VU6WC5rPQB4Ld1jnLV7Kd5xr+cq9avvhwqzbH/taCRNURoeEpoPBK9pDyeukwSxwRPJ8fDgvYXd6SkaZ2TA=="
      crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ url ('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- ChartJS -->
    <!-- <script src="{{ url ('plugins/chart.js/Chart.min.js') }}"></script> -->
    <!-- Sparkline -->
    <!-- <script src="{{ url ('plugins/sparklines/sparkline.js') }}"></script> -->
    <!-- JQVMap -->
    <!--Scripts for JQMap are deleted -->
    <!-- jQuery Knob Chart -->
    <script src="{{ url ('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment-with-locales.min.js"></script>
    <script src="{{ url ('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ url ('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ url ('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ url ('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ url ('js/admin_js/adminlte.js') }}"></script>
    <!-- Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{ url ('js/admin_js/pages/dashboard.js') }}"></script>
    <!-- toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    @if(Session::has('message'))
    <script>
      var type = "{{ Session::get('alert-type', 'info') }}";
      switch (type) {
        case 'info':
          toastr.info("{{ Session::get('message') }}");
          break;

        case 'warning':
          toastr.warning("{{ Session::get('message') }}");
          break;

        case 'success':
          toastr.success("{{ Session::get('message') }}");
          break;

        case 'error':
          toastr.error("{{ Session::get('message') }}");
          break;
      }

    </script>
    @endif

    @if(count($errors) > 0)
    @foreach($errors->all() as $error)
    <script>
      //$('#add').modal('show');
      toastr.error("{{ $error }}");
      toastr.options = {
        "preventDuplicates": true
      }
    </script>
    @endforeach
    @endif


    <!-- Datatables script-->
    <script type="text/javascript" charset="utf8"
      src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8"
      src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" charset="utf8"
      src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>



    <script>
      $(function () {
        $('[data-toggle="tooltip"]').tooltip()
      })
    </script>
    <!--DropZone-->
    <script src="{{ url('dropzone-5.7.0/dist/min/dropzone.min.js') }}"></script>
    <!-- File Pond -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/filepond/4.30.3/filepond.min.js"
      integrity="sha512-AuMJiyTn/5k+gog21BWPrcoAC+CgOoobePSRqwsOyCSPo3Zj64eHyOsqDev8Yn9H45WUJmzbe9RaLTdFKkO0KQ=="
      crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- sweet alert -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="{{ url('js/admin_js/script.js') }}"></script>
    <script src="{{ url('js/custom.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-de-DE.min.js"
      integrity="sha512-2C5K3vx127hx1xmHot25EPLCOVYyqPr6F8A9UKPtmoYEL3la45Etrq5E7nby09as3cjMLzrxFJzslw1OFEOq4A=="
      crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Bootstrap table JS -->
    <script src="https://unpkg.com/bootstrap-table@1.19.1/dist/bootstrap-table.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.7/dist/cdn.min.js
"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script> -->



    <script type="text/javascript">

      //     $(document).ready(function() {
      //     // Use a more specific selector if necessary to target only form submit buttons
      //     $('button[type="submit"]').click(function() {
      //         var button = $(this); // Get the button that was clicked
      //         button.prop('disabled', true); // Disable it
      //         button.text('Hmmmm...'); // Optional: change text

      //         // Get the form to which the button belongs
      //         var form = button.closest('form');

      //         // Optional: Check if the form is valid if you're using any form validation plugin
      //         if(form.valid()) {
      //             form.submit(); // If valid, submit the form
      //         } else {
      //             button.prop('disabled', false); // If not, re-enable the button
      //             button.text('Einreichen'); // Optional: Reset button text
      //         }

      //     });
      // });

      $(document).ready(function () {
        $('form').on('submit', function (e) {
          var $form = $(this);
          var $submitButton = $form.find('button[type="submit"], input[type="submit"]');
          if ($submitButton.length > 0) {
            $submitButton.prop('disabled', true);
            $submitButton.html('Processing...'); // Optional: change button text to show it's processing
          }
        });
      });


      $('.notizen').summernote({
        height: 150,
        lang: 'de-DE',
        lineNumbers: true,
        lineWrapping: true,
        callbacks: {
          onPaste: function (e) {
            var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
            e.preventDefault();
            document.execCommand('insertText', false, bufferText);
          }
        }
      });


      setInterval(function () {
        $('#notification_bell').load(document.URL + ' #notification_bell');
        console.log('notification refreshed');
      }, 10000 * 5);
    </script>

    <script>
      function markNotificationAsRead(notificationId, redirectTo) {
        $.ajax({
          url: '/mark-notification-read/' + notificationId,
          method: 'GET',
          success: function () {
            window.location.href = redirectTo;
          },
          error: function () {
            // Handle the error if the AJAX request fails
            alert('Error marking notification as read.');
          }
        });
      }
    </script>


    <!-- Video js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/js/all.min.js"></script>
    <script src="https://vjs.zencdn.net/7.18.1/video.min.js"></script>
    @yield('script')
</body>

</html>