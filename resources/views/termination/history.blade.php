@extends('layouts.admin_layout.admin_layout')

<style>

</style>
@section('content')

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid col-lg-12">
        <div class="row">
          <div class="col-12 mx-auto">
            <!-- Profile Image -->
            <!-- child cards -->
            <div class="row mx-auto">
              <!-- first card -->
              <div class="col-lg-9 mx-auto">
                <div class="container-fluid">
                  <div class="d-flex justify-content-between col-md-12">
                    <div>
                      <button class="btn btn-outline-primary" onclick="window.history.back(1);"><i
                          class="fas fa-long-arrow-alt-left fa-lg"></i></button>
                    </div>
                    <div class="mr-5">
                      <h1 class="ticket_header">Wiederherstellen</h1>
                    </div>
                    <div>
                    </div>
                  </div>
                </div><!-- /.container-fluid -->
                <div class="card card-primary card-outline">
                  <div class="position-relative">
                    <div class="card-body box-profile form-group">
                      <div class="row">
                        <div class="col-md-12">

                          <div class="card-body p-0">
                            <div class="mailbox-messages">
                              <table id="history_table" class="display nowrap table-sm" style="width:100%">
                                <thead>
                                  <tr>
                                    <th>Name</th>
                                    <th>Austritt zum</th>
                                    <th>Standort</th>
                                    <th>Beschäftigung</th>
                                    <th>Wiederherstellen</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($terminations as $termination)
                                  <tr>
                                    <td>{{$termination->name}}</td>
                                    <td><span class="d-none">{{$termination->exit->format('Ymd')}}</span> {{$termination->exit->format('d.m.Y')}}</td>
                                    <td>{{$termination->location}}</td>
                                    <td>{{$termination->occupation}}</td>

                                    <td>
                                      <a class="btn btn-outline-success float-right"
                                        href="{{ route('termination.restore', $termination->id) }}"><i
                                          class="fa-solid fa-trash-arrow-up"></i></a>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                              </table>
                              <!-- /.table -->
                            </div>
                            <!-- /.mail-box-messages -->
                          </div>

                        </div>
                      </div>
                      <!-- /.card-body -->
                    </div>
                  </div>
                </div>
              </div>
              <!--end first card -->
              <!-- second card -->
              <div class="col-lg-3">
                <!-- Card  -->
                <div class="card card-primary card-outline" style="background-color: #661421;">
                  <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#fff;"><strong>Shortcut-Panel</strong></h5>
                    <div class="card-text">
                      <div class="list-group">
                        <a href="{{route ('profile')}}"
                          class="list-group-item list-group-item-action list-group-item-primary py-1"><i
                            class="far fa-user fa-lg"></i><strong> Eigenes Profil bearbeiten</strong></a>
                        <a href="{{ url('/contacts') }}"
                          class="list-group-item list-group-item-action list-group-item-primary py-1"><i
                            class="far fa-address-book fa-lg"></i><strong> Adressbuch</strong></a>
                        <a href="{{ route('ticket.index') }}"
                          class="list-group-item list-group-item-action list-group-item-primary py-1"><i
                            class="fas fa-ticket-alt fa-lg"></i><strong> Ticket erstellen</strong>
                          <a href="{{ route('ticket.usertickets') }}"
                            class="list-group-item list-group-item-action list-group-item-primary py-1"><i
                              class="fas fa-clipboard-list fa-lg"></i><strong> Meine Tickets</strong>
                          </a>
                      </div><!-- End Linst Group -->
                    </div><!-- End Card Text -->
                  </div><!-- End Card Body -->
                </div> <!-- End Card  -->
              </div><!-- End First Section -->
              <!--end second card -->
            </div>
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
    <!-- /.content-wrapper -->
  </div>
</section>

@endsection

@section('script')

<script>

  $('#history_table').DataTable({
    searching: true,
    pageLength: 50,
    paging: true,
    info: true,
    responsive: true,
    autoWidth: true,
    order:[[1, 'desc']],
    "language": {
  "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/German.json"
},
    columnDefs: [
      { targets: 4, width: "1%" },
      { "orderable": false, "targets": 4 },
    ]
  });

// $(function() {
// $.datepicker.setDefaults($.datepicker.regional["de"]);

//   $('.exit').daterangepicker({
//       autoUpdateInput: false,
//       singleDatePicker: true,
//       locale: {
//           cancelLabel: 'Clear'
//       }
//   });

//   $('.exit').on('apply.daterangepicker', function(ev, picker) {
//       $(this).val(picker.startDate.format('MM/DD/YYYY'));
//   });

//   $('.exit').on('cancel.daterangepicker', function(ev, picker) {
//       $(this).val('');
//   });

// });

</script>

@endsection