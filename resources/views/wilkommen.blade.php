@extends('layouts.admin_layout.admin_layout')

<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">

<style>
  table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
  table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
    background-color: #661421 !important;
    content: '👁' !important;
  }

  /* datatable - hidden column wrapping */
  .dtr-data {
    white-space: normal
  }
</style>

@section('content')

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- End News -->
    <section class="content-header text-center">
      <div class="container fluid">
        <div class="row">
          <div class="col-12 mx-auto">
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
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
                            <div class="col-md-8 pl-0 ml-0">
                              <div class="card card-primary card-outline">
                                <div class="card-header">
                                  <div class="float-left">
                                    <h3 class="card-title text-bold">Lizenzen</h3>
                                  </div>
                                  <div class="float-right">
                                    <a class="btn btn-outline-success" href="{{ route('licenses.create') }}"><i
                                        class="fa-solid fa-plus"></i></a>
                                  </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                  <div class="mailbox-messages">
                                    <table id="reminder_table" class="display nowrap table-sm" style="width:100%">
                                      <thead>
                                        <tr>
                                          <th>Lizenzname</th>
                                          <th>Wo</th>
                                          <th class="text-left">Bemerkung</th>
                                          <th>Gültig</th>
                                          <th>Version</th>
                                          <th class="text-center">Ändern</th>

                                        </tr>
                                      </thead>
                                      <tbody>
                                        @foreach($licenses as $license)
                                        <tr>
                                          <td>{{$license->name}}</td>
                                          <td>{{$license->where}}</td>
                                          <td class="text-left">{{$license->comment}}</td>
                                          @if(is_null($license->valid))
                                          <td></td>
                                          @elseif($license->valid->lte($week))
                                          <td class="text-bold" style="color: red;">{{$license->valid->format('d.m.Y')}}
                                          </td>
                                          @elseif($license->valid->lte($month))
                                          <td class="text-bold" style="color: orange;">
                                            {{$license->valid->format('d.m.Y')}}</td>
                                          @else
                                          <td class="text-bold" style="color: green;">
                                            {{$license->valid->format('d.m.Y')}}</td>
                                          @endif
                                          <td>{{$license->version}}</td>
                                          <td>
                                            <form action="{{ route('licenses.destroy',$license->id) }}" method="POST">
                                              <a class="btn btn-outline-dark btn-sm"
                                                href="{{ route('licenses.edit',$license->id) }}"><i
                                                  class="fa-solid fa-pencil"></i></a>
                                              @csrf
                                              @method('DELETE')
                                              <button type="submit" class="btn btn-sm btn-danger"><i
                                                  class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                          </td>
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
                            <div class="col-md-4 pl-0 ml-0">
                              <div class="card card-primary card-outline">
                                <div class="card-header">
                                  <div class="float-left">
                                    <h3 class="card-title text-bold">Kündigungen</h3>
                                  </div>
                                  <div class="float-right">
                                    <a class="btn btn-outline-primary" href="{{ route('termination_history') }}"><i
                                        class="fa-solid fa-clock-rotate-left"></i></a>
                                    <a class="btn btn-outline-success" href="{{ route('terminations.create') }}"><i
                                        class="fa-solid fa-plus"></i></a>
                                  </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                  <div class="mailbox-messages">
                                    <table id="termination_table" class="display nowrap table-sm" style="width:100%">
                                      <thead>
                                        <tr>
                                          <th>Name</th>
                                          <th>Austritt zum</th>
                                          <th>Status</th>
                                          <th>Standort</th>
                                          <th>Beschäftigung</th>
                                          <th class="text-center">Status wechseln</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @foreach($terminations as $termination)
                                        <tr>
                                          @php
                                            $isInactive = !$termination->is_active;
                                            $exitDate = $termination->exit;
                                            $dateColor = null;
                                            if ($exitDate) {
                                                if ($isInactive) {
                                                    $dateColor = '#343a40';
                                                } elseif ($exitDate->lte($week)) {
                                                    $dateColor = 'red';
                                                } elseif ($exitDate->lte($month)) {
                                                    $dateColor = 'orange';
                                                } else {
                                                    $dateColor = 'green';
                                                }
                                            }
                                          @endphp
                                          <td>
                                            <button type="button"
                                              class="btn btn-link p-0 text-left termination-action-trigger"
                                              data-toggle="modal" data-target="#terminationActionModal"
                                              data-id="{{ $termination->id }}"
                                              data-name="{{ $termination->name }}"
                                              data-edit-url="{{ route('terminations.edit', $termination->id) }}"
                                              data-delete-url="{{ route('termination_delete', $termination->id) }}">
                                              {{$termination->name}}
                                            </button>
                                          </td>
                                          @if(is_null($exitDate))
                                          <td></td>
                                          @else
                                          <td class="text-bold" style="color: {{$dateColor}};">
                                            {{$exitDate->format('d.m.Y')}}</td>
                                          @endif
                                          <td class="text-center">
                                            @if($isInactive)
                                            <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                                            <span class="sr-only">Inaktiv</span>
                                            @else
                                            <span class="sr-only">Aktiv</span>
                                            @endif
                                          </td>
                                          <td>{{$termination->location}}</td>
                                          <td>{{$termination->occupation}}</td>

                                          <td class="text-center">
                                            <form action="{{ route('terminations.toggle', $termination) }}" method="POST"
                                              class="d-inline">
                                              @csrf
                                              <button type="submit"
                                                class="btn btn-sm {{ $isInactive ? 'btn-secondary' : 'btn-success' }}"
                                                title="{{ $isInactive ? 'Als aktiv markieren' : 'Als inaktiv markieren' }}">
                                                <i class="fa-solid {{ $isInactive ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                                <span class="sr-only">{{ $isInactive ? 'Aktivieren' : 'Deaktivieren' }}</span>
                                              </button>
                                            </form>
                                          </td>
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
              <!--end first card -->
              <!-- second card -->
              <!-- <div class="col-lg-3">
                        <div class="card card-primary card-outline" style="background-color: #661421;">
                          <div class="card-body">
                            <h5 class="card-title mb-3" style="color:#fff;"><strong>Shortcut-Panel</strong></h5>
                            <div class="card-text">
                              <div class="list-group">
                                <a href="{{route ('profile')}}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="far fa-user fa-lg"></i><strong> Eigenes Profil bearbeiten</strong></a>
                                <a href="{{ url('/contacts') }}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="far fa-address-book fa-lg"></i><strong> Adressbuch</strong></a>
                                <a href="{{ route('ticket.index') }}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-ticket-alt fa-lg"></i><strong> Ticket erstellen</strong>
                                <a href="{{ route('ticket.usertickets') }}" class="list-group-item list-group-item-action list-group-item-primary py-1"><i class="fas fa-clipboard-list fa-lg"></i><strong>     Meine Tickets</strong>
                                </a>
                              </div>
                            </div>
                          </div>
                        </div> 
                      </div> -->
              <!-- End First Section -->
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

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

<div class="modal fade" id="terminationActionModal" tabindex="-1" role="dialog"
  aria-labelledby="terminationActionModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="terminationActionModalLabel">Eintrag bearbeiten</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Wählen Sie eine Aktion für <strong id="terminationModalName"></strong>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
        <a id="terminationEditLink" class="btn btn-outline-dark" href="#">
          <i class="fa-solid fa-pencil mr-1"></i> Bearbeiten
        </a>
        <button id="terminationDeleteButton" type="button" class="btn btn-danger">
          <i class="fa-solid fa-trash-can mr-1"></i> Löschen
        </button>
      </div>
    </div>
  </div>
</div>

@endsection


@section('script')


<script>



  $(document).ready(function () {


    $('#termination_table').DataTable({
      searching: false,
      paging: false,
      info: false,
      order: [],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details für ' + data[0];
            }
          }),
          renderer: $.fn.dataTable.Responsive.renderer.tableAll()
        }
      }
    });



    $('#reminder_table').DataTable({
      searching: false,
      paging: false,
      info: false,
      order: [],
      responsive: true
    });
  });


  $('#terminationActionModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var name = button.data('name');
    var editUrl = button.data('edit-url');
    var deleteUrl = button.data('delete-url');
    var id = button.data('id');

    var modal = $(this);
    modal.find('#terminationModalName').text(name);
    modal.find('#terminationEditLink').attr('href', editUrl);

    var deleteButton = modal.find('#terminationDeleteButton');
    deleteButton.data('id', id);
    deleteButton.data('action', deleteUrl);
  });

  $('#terminationDeleteButton').on('click', function () {
    var button = $(this);
    var id = button.data('id');
    var action = button.data('action');
    $('#terminationActionModal').modal('hide');
    deleteConfirmation2(id, action);
  });


  function deleteConfirmation2(id, actionUrl) {
    Swal.fire({
      title: 'sind Sie sicher ?',
      text: "Sie können dies nicht rückgängig machen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#661421',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ja, löschen !',
      cancelButtonText: 'Nein !'
    }).then(function (e) {
      if (e.value === true) {
        console.log('its worked till here')
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
          type: 'POST',
          url: actionUrl || "{{url('/terminations.delete')}}/" + id,
          data: { _token: CSRF_TOKEN },
          success: function (results) {
            if (results === 'true') {
              location.reload();
            } else {
              console.log('its in else')
            }
          }
        });

      } else {
        e.dismiss;
      }

    }, function (dismiss) {
      return false;
    })
  }
</script>


@endsection
