@extends('layouts.admin_layout.admin_layout')


@section('content')
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
              <div class="col-lg-12">
                <div class="card card-primary card-outline">
                  <div class="position-relative">
                    <div class="card-body box-profile form-group">
                      <div class="row">
                        <div class="col-md-12">

                          @if(auth()->user()->hasRole('HR') || auth()->user()->hasRole('Super_Admin'))
                          <div class="row">
                            <div class="col-md-12 pl-0 ml-0">
                              <div class="card card-primary card-outline">
                                <div class="card-header">
                                  <div class="float-left">
                                    <h3 class="card-title text-bold">Mitarbeiter Liste</h3>
                                  </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-2">
                                  <div class="mailbox-messages">
                                    <table id="employee_table" class="display nowrap table-sm " style="width:100%">
                                      <thead>
                                        <tr>
                                          <th>Name</th>
                                          <th>Benutzername</th>
                                          <th>Email</th>
                                          <th>Telefon</th>
                                          <th>Adresse</th>
                                          <th>Position</th>
                                          <th>Abteilung</th>
                                          <th class="text-center">IS+</th>
                                          <th>Ticket Ersteller</th>
                                          <th>Erstellt AM</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                      @foreach($employees as $employee)
                                        <tr>
                                          <td>{{ $employee->empLastName }}, {{ $employee->empFirstName }}</td>
                                          <td>{{ $employee->empUsername }}</td>
                                          <td>{{ $employee->empEmail }}</td>
                                          <td>{{ $employee->empTelefon }}</td>
                                          <td>{{ $employee->location }}</td>
                                          <td>{{ $employee->empPosition }}</td>
                                          <td>{{ $employee->empAbteilung }}</td>
                                          <td class="text-center"><input type="checkbox" checked="{{ $employee->empISplus }}"></td>
                                          <td>{{ $employee->Ticketsubmitter }}</td>
                                          <td>{{ $employee->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                        @endforeach
                                      </tbody>
                                    </table>
                                    <!-- /.table -->
                                  </div>
                                  <!-- /.mail-box-messages -->
                                </div>
                                <!-- /.card-body -->
                                <!-- /.card -->
                              </div>
                            </div>
                          </div>

                          @endif


                        </div>
                        <!-- <div class="col-sm-6">
                              <p></p>
                            </div> -->
                      </div>

                      <!-- /.card-body -->
                    </div>
                  </div>
                </div>
              </div>
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
$(document).ready(function() {

    
    $('#employee_table').DataTable( {
     language: {
        url: "https://cdn.datatables.net/plug-ins/1.10.18/i18n/German.json",
      },
     searching: true, 
      paging: false, 
      info: false,
      order: [],
    } );
});
</script>


@endsection