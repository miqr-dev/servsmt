@extends('layouts.admin_layout.admin_layout')

<style>

</style>
@section('content')
<!-- Main content -->
<section class="content-fluid">
  <div class="row">
    <div class="col-md-11 mx-auto"></div>
    <!-- /.col -->
    <div class="col-md-11 mx-auto">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title text-bold"></h3>
          <a href="#" class="btn btn-outline-primary" data-toggle="modal" data-target="#bentuzer_liste">
            Video Anschauen
          </a>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <div class="mailbox-messages display nowrap" style="width: 100%;">
            <table class="display responsive compact table-sm" id="participant_list_table">
              <thead>
                <tr class="text-center">
                  <th>Nr.</th>
                  <th>Vorname</th>
                  <th>Nachname</th>
                  <th>Benutzername</th>
                  <th>Passwort</th>
                  <th>Maßnahme</th>
                  <th>Email</th>
                  <th>Erstellt am</th>
                  <th>Erledigt am</th>
                  <th>Erledigt von</th>
                  <th class="d-none export-only">Alter</th>
                  <th class="d-none export-only">Geb.datum</th>
                  <th class="d-none export-only">Dauer</th>
                  <th class="d-none export-only">Beginn</th>
                  <th class="d-none export-only">Ende</th>
                  <th class="d-none export-only">Berater</th>
                  <th class="d-none export-only">MA/Team</th>
                  <th class="d-none export-only">Bemerkungen</th>
                  <th class="d-none export-only">A 1.TT</th>
                  <th class="d-none export-only">A 2.TT</th>
                  <th class="d-none export-only">User-type</th>
                  <th class="d-none export-only">Deaktivierungsdatum</th>
                  <th class="d-none export-only">Kurse</th>
                  <th class="d-none export-only">Gruppe</th>
                  <th class="d-none export-only">Branch</th>
                </tr>
              </thead>
              <tbody>
                @foreach($participants as $participant)
                <tr class="text-center">
                  <td></td> <!-- Nr. -->
                  <td>{{$participant->vorname}}</td>
                  <td>{{$participant->nachname}}</td>
                  <td style="font-weight: bold;">{{$participant->username}}</td>
                  <td style="font-family: 'Courier Prime', monospace;">{{$participant->password}}</td>
                  <td>{{@$participant->course}}</td>
                  <td>{{@$participant->email}}</td>
                  <td style="color: blue;">
                    {{\Carbon\carbon::parse($participant->ticket['created_at'])->format('d-m-Y')}}</td>
                  <td style="color: green;">{{$participant->created_at ? $participant->formatted_created_at : ''}}</td>
                  <td>{{@$participant->ticket['done_by']}}</td>
                  <td></td> <!-- Alter -->
                  <td></td> <!-- Geb.datum -->
                  <td></td> <!-- Dauer -->
                  <td></td> <!-- Beginn -->
                  <td></td> <!-- Ende -->
                  <td></td> <!-- Berater -->
                  <td></td> <!-- MA/Team -->
                  <td></td> <!-- Bemerkungen -->
                  <td></td> <!-- A 1.TT -->
                  <td></td> <!-- A 2.TT -->
                  <td class="d-none export-only">Learner-Type</td>
                  <td class="d-none export-only">{{ @$participant->deaktivierungsdatum }}</td>
                  <td class="d-none export-only">{{ @$participant->kurs }}</td>
                  <td class="d-none export-only">{{ @$participant->gruppe }}</td>
                  <td class="d-none export-only">{{ @$participant->branch }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>


            <!-- /.table -->
          </div>
          <!-- /.mail-box-messages -->
        </div>
        <!-- /.card-body -->
        <div class="card-footer p-0">
          <div class="mailbox-controls">
            <div class="float-right">
              <div class="btn-group">
                <!-- <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-left"></i></button>
                  <button type="button" class="btn btn-default btn-sm"><i class="fas fa-chevron-right"></i></button> -->
              </div>
              <!-- /.btn-group -->
            </div>
            <!-- /.float-right -->
          </div>
        </div>
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
  <!-- Modal -->
  <div class="modal fade" id="bentuzer_liste" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
    data-backdrop="false">
    <div class="modal-dialog" style="max-width: 1200px !important;">
      <div class="modal-content container">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <video fluid="true" id="my-video" class="video-js vjs-theme-city" controls preload="auto" width="600"
            height="400" data-setup="{}">
            <source src="/images/admin_images/Teilnehmer_liste.mp4" type="video/mp4" />
          </video>
        </div>
      </div>
    </div>
  </div>



</section>

@endsection

@section('script')
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/select/1.3.4/js/dataTables.select.min.js"></script>
<script>
  $(document).ready(function () {
    var table = $("#participant_list_table").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.10.18/i18n/German.json",
      },
      bSort: false,
      dom: "Bfrtip",
      pageLength: 50,
      responsive: true,
      autoWidth: false,
      columnDefs: [
        {
          targets: 0, // Apply to the "Nr." column
          render: function (data, type, row, meta) {
            return meta.row + 1; // Return the row number + 1 for 1-based index
          }
        },
        {
          targets: 'export-only',
          visible: false,
          exportOptions: {
            columns: ':visible'
          }
        }
      ],
      buttons: [
        {
          extend: 'copy',
          className: 'btn btn-outline-primary',
          exportOptions: {
            columns: [1, 2, 3, 4, 5],
          }
        },
        {
          extend: 'csv',
          className: 'btn btn-outline-primary',
          exportOptions: {
            columns: [1, 2, 3, 4, 5],
          }
        },
        {
          extend: "excel",
          className: "btn btn-outline-primary",
          exportOptions: {
            columns: [1, 2, 3, 6, 4, 10, 5, 21, 22, 23, 24] // Adjust column indexes as needed
          }
        },
        {
          extend: 'pdf',
          className: 'btn btn-outline-primary',
          exportOptions: {
            columns: [1, 2, 3, 4, 5],
          }
        },
        {
          extend: "print",
          text: "Auswahl drucken",
          className: 'btn btn-outline-primary',
          exportOptions: {
            columns: [1, 2, 3, 4, 5],
            modifier: {
              selected: true,
              search: "none"
            }
          }
        },
        {
          extend: "print",
          text: "Alle drucken (nicht nur ausgewählte)",
          className: 'btn btn-outline-primary',
          exportOptions: {
            columns: [1, 2, 3, 4, 5],
            modifier: {
              selected: null
            }
          }
        },
        // New Excel export button with different column order
        {
          extend: "excel",
          text: "Excel Erweitert",
          className: "btn btn-outline-primary",
          exportOptions: {
            columns: [0, 1, 2, 5, 6, 10, 11, 12, 13, 14, 15, 16, 17, 3, 4, 18, 19] // Adjust column indexes as needed
          },
          customize: function (xlsx) {
            var sheet = xlsx.xl.worksheets['sheet1.xml'];
            var rowIndex = 1;

            // Loop through each row in the sheet and update the first cell (Nr. column)
            $('row', sheet).each(function () {
              var r = parseInt($(this).attr('r')); // Get the row number as integer
              if (r > 2) { // Skip the header row
                var cell = $(this).find('c[r^="A"]'); // Target cell in the "A" column
                if (cell.length) {
                  cell.find('v').text(rowIndex++); // Set the cell value to the incrementing row index
                }
              }
            });
          }
        },
        // New custom print button
        {
          extend: "print",
          text: "PC-Zugänge Drucken",
          className: 'btn btn-outline-primary',
          exportOptions: {
            columns: [1, 2, 3, 4, 5], // Adjust this if you need to print more columns
            modifier: {
              selected: true,
              search: "none"
            }
          },
          customize: function (win) {
            var body = $(win.document.body);
            body.css('font-size', '20pt')  // Increase font size to 20pt
              .css('margin', '0');

            body.find('table')
              .removeClass('dataTable')
              .addClass('compact')
              .css('width', '100%')
              .css('font-size', 'inherit');

            // Custom print layout
            var selectedRows = body.find('table tbody tr');
            var content = '<div style="display: flex; flex-wrap: wrap; gap: 20px; padding: 20px;">';  // Increase gap and padding

            selectedRows.each(function (index) {
              var cells = $(this).find('td');
              var fullName = `${$(cells[0]).text()} ${$(cells[1]).text()}`;
              content += `
      <div style="width: 48%; padding: 20px; border: 1px solid black; margin-bottom: 20px;">
        <div><strong>Zugangsdaten für:</strong></div>
        <div>${fullName}</div>
        <div style="margin-top: 10px;"><strong>Anmeldedaten PC</strong></div>
        <div>Benutzername: ${$(cells[2]).text()}</div>
        <div>Kennwort: ${$(cells[3]).text()}</div>
      </div>`;
            });

            content += '</div>';
            body.html(content);
          }
        }


      ],
      select: {
        style: "os"
      }
    });

    $('body').on('hidden.bs.modal', '.modal', function () {
      $('video').trigger('pause');
    });
  });

</script>

@endsection