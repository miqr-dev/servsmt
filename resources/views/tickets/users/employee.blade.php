@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Neuer Mitarbeiter'])
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
              @csrf
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-primary card-outline">
                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Neuer Mitarbeiter">
                      <div class="card-body box-profile form-group">
                        <div class="row col-md-12">
                          <div class="form-group col-md-6">
                            <div class="row">
                              <div class="form-group col-md-8">
                                <label for="replication"> Berechtigungen wie bei &nbsp;<i
                                    class="fas fa-feather-alt fa-lg" style="color:#661421;"></i></label>
                                <select class="custom-select form-control mb-2 replication" name="replication_id"
                                  required>
                                  <option class="form-control" value=""></option>
                                  @foreach($users as $user)
                                  <option class="form-control" value="{{$user['id']}}">{{$user['name']}},
                                    {{$user['vorname']}}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="form-group col-md-4">
                                <label for="employee_required_at">Beginnt am</label>
                                <!-- required until -->
                                <input type="text" class="form-control empstartdate" name="employee_required_at"
                                  required>
                              </div>

                            </div>
                            <div class="row mt-2">
                              <div class="form-group col-md-6">
                                <label for="submitter">Nachname &nbsp;<i class="fas fa-feather-alt fa-lg"
                                    style="color:#661421;"></i></label>
                                <input type="text" class="form-control" name="employee_lastname" required>
                              </div>
                              <div class="form-group col-md-6">
                                <label for="submit_date">Vorname &nbsp;<i class="fas fa-feather-alt fa-lg"
                                    style="color:#661421;"></i></label>
                                <input type="text" class="form-control" name="employee_firstname" required>
                              </div>
                              <div class="form-group col-md-12">
                                <label for="employee_finish_at">Endet zum</label>
                               <small class="form-text text-muted" style="display: inline;">Nur Bei Freien MA.</small>
                                <!-- finish until -->
                                <input type="text" class="form-control empenddate" name="employee_finish_at">
                              </div>
                            </div>
                            <div class="container-fluid">
                              <label for="replication" class="mt-4">Benutzerdaten senden an &nbsp;</label>
                              <div class="row">
                                <div class="col-sm">
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_erfurt"
                                      name="email_erfurt" value="Sekretariat_Erfurt@miqr.de">
                                    <label class="custom-control-label" for="email_erfurt">Sek. Erfurt</label>
                                  </div>
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_berlin"
                                      name="email_berlin" value="Sekretariat_Berlin@miqr.de">
                                    <label class="custom-control-label" for="email_berlin">Sek. Berlin</label>
                                  </div>
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_leipzig"
                                      name="email_leipzig" value="Sekretariat_Leipzig@miqr.de">
                                    <label class="custom-control-label" for="email_leipzig">Sek. Leipzig</label>
                                  </div>
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_dresden"
                                      name="email_dresden" value="Sekretariat_Dresden@miqr.de">
                                    <label class="custom-control-label" for="email_dresden">Sek. Dresden</label>
                                  </div>
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_chemnitz"
                                      name="email_chemnitz" value="Sekretariat_Chemnitz@miqr.de">
                                    <label class="custom-control-label" for="email_chemnitz">Sek. Chemnitz</label>
                                  </div>
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_suhl"
                                      name="email_suhl" value="Sekretariat_Suhl@miqr.de">
                                    <label class="custom-control-label" for="email_suhl">Sek. Suhl</label>
                                  </div>
                                </div>
                                <div class="col-sm">
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_lorenz"
                                      name="email_lorenz" value="Martin.Lorenz@miqr.de">
                                    <label class="custom-control-label" for="email_lorenz">Herrn Lorenz</label>
                                  </div>
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_lasch"
                                      name="email_lasch" value="Antje.Lasch-Rosenhoff@miqr.de">
                                    <label class="custom-control-label" for="email_lasch">Frau Lasch</label>
                                  </div>
                                  <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_kirchner"
                                      name="email_kirchner" checked onchange="this.checked = !this.checked"
                                      value="Matthias.Kirchner@miqr.de">
                                    <label class="custom-control-label" for="email_kirchner">Herrn Kirchner</label>
                                  </div>
                                  <!-- <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_ockel"
                                      name="email_ockel" checked onchange="this.checked = !this.checked"
                                      value="Alexander.Ockel@miqr.de">
                                    <label class="custom-control-label" for="email_ockel">Herrn Ockel</label>
                                  </div> -->
                                  <div class="custom-control custom-checkbox ">
                                    <input type="checkbox" class="custom-control-input" id="email_test" checked
                                      onchange="this.checked = !this.checked">
                                    <label class="custom-control-label" for="email_test">{{ auth()->user()->username
                                      }}</label>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="row mt-1 p-3">
                              <div>
                                <label for="email_custom">Andere Email</label>
                                <div style="margin-top: -5px;">
                                  <small class="form-text text-muted" style="display: inline;">Mehrere E-Mails? Bitte
                                    trennen Sie sie mit einem Semikolon.</small><strong style="color: blue;"> ( ; )
                                  </strong>
                                </div>
                              </div>
                              <input type="text" class="form-control" name="email_custom">
                            </div>

                          </div>
                          <div class="form-group col-md-6">
                            <fieldset class="border rounded px-2 mb-2">
                              <legend class="w-auto"><i class="fas fa-clipboard-list" style="color:green;"></i></legend>
                              <label for="employee_place"> Standort &nbsp;<i class="fas fa-feather-alt fa-lg"
                                  style="color:#661421;"></i></label>
                              <select class="custom-select form-control mb-2" id="employee_place" name="location_id"
                                required>
                              </select>
                              <label for="position_employee"> Position &nbsp;<i class="fas fa-feather-alt fa-lg"
                                  style="color:#661421;"></i></label>
                              <input type="text" class="form-control mb-2" id="position_employee"
                                name="position_employee" required>
                              <label for="abteilung_employee"> Abteilung &nbsp;<i class="fas fa-feather-alt fa-lg"
                                  style="color:#661421;"></i></label>
                              <input type="text" class="form-control mb-2" id="abteilung_employee"
                                name="abteilung_employee" required>
                              <label for="telephone_employee"> Telefon &nbsp;<i class="fas fa-feather-alt fa-lg"
                                  style="color:#661421;"></i></label>
                              <input type="text" class="form-control mb-2" id="telephone_employee"
                                name="telephone_employee" required>
                              <div class="col-md-12 d-flex justify-content-around">
                                <div class="custom-control custom-checkbox mb-3">
                                  <input type="checkbox" class="custom-control-input" id="outlook" name="outlook">
                                  <label class="custom-control-label" for="outlook">Outlook</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-3">
                                  <input type="checkbox" class="custom-control-input" id="isplus" name="isplus">
                                  <label class="custom-control-label" for="isplus">IS+</label>
                                </div>
                              </div>
                            </fieldset>
                          </div>
                          @include('tickets.layout_ticket.note',['discription'=>'Beschreibung'])
                        </div>
                        <div>
                          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--end second card -->
              </div>
            </form>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
@endsection

@section('script')
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $(document).ready(function () {
    let selectAddresslisten = new Array();
    let roomlisten = new Array();
    $('#employee_place').find('option').remove();
    $('#employee_place').find('optgroup').remove();
    $('#employee_place').append(new Option("Standort...", ''));
    $.ajax({
      type: "get",
      url: "{{route('item.listen')}}",
    }).done(function (data) {
      selectAddresslisten = new Array();
      $.each(data['places'], function (index, item) {
        $("#employee_place").append('<optgroup label="' + index + '" id="' + item + '" ></optgroup>');
      });
      $.each(data['locations'], function (index, item) {
        $("#employee_place #" + item.place_id).append(new Option(item.address, item.id));
        selectAddresslisten.push(item);
      });
    });

    $(".replication").select2({
      placeholder: 'Bitte Wählen',
      allowClear: false,
      tags: false
    });


    $(function () {
      moment.locale('de');
      $('.empstartdate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        startDate: moment().add(7, 'day'),
        minDate: moment(),
        minYear: parseInt(moment().format('YYYY')) - 1,
        maxYear: parseInt(moment().format('YYYY')) + 1,
        opens: 'center',
        locale: {
          format: 'DD-MM-YYYY'
        }
      });
      $('.empenddate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false, // Ensures the input is not automatically filled
        minYear: parseInt(moment().format('YYYY')) - 1,
        maxYear: parseInt(moment().format('YYYY')) + 1,
        opens: 'center',
        locale: {
          format: 'DD-MM-YYYY',
          cancelLabel: 'Clear' // Adds a "Clear" button to the picker
        }
      });

      // This will update the input when a date is selected
      $('.empenddate').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY'));
      });

      // This will clear the input if the user cancels the date picker
      $('.empenddate').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
      });

      // Clear the input on page load (ensures the field is empty initially)
      $('.empenddate').val('');
    });

  })
</script>
@endsection