@extends('layouts.admin_layout.admin_layout')
@section('content')
@include('tickets.layout_ticket.header',['title'=>'Email Weiterleiten'])
<!-- Main content -->
<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store')}}" method="post" id="ticket_forms">
              @csrf
              <!-- child cards -->
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('tickets.layout_ticket.submitter')
                <!--end Submitter Section -->
                <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-primary card-outline">
                    <div class="card-body box-profile form-group">
                    </div>
                    <div id="underform">
                      <!-- ! Jquery forms here -->
                      <input type="hidden" name="problem_type" value="Email Weiterleitung">
                      <div class="card-body box-profile form-group">
                        <div class="row col-md-12">
                          <div class="form-group col-md-12">
                            <div class="row col-md-12">
                              <div class="form-group col-md-6">
                                <label for="forward_on">Weiterleitung an: &nbsp;<i class="fas fa-feather-alt fa-lg"
                                    style="color: #661421;"></i>
                                  &nbsp;<i class="fa-solid fa-right-to-bracket fa-lg" style="color:green;"></i></label>
                                <select class="form-control forward-user-select" name="forward_on" required>
                                  <option value="">Mitarbeiter</option>
                                  @foreach($users as $availableUser)
                                  <option value="{{ $availableUser['id'] }}">
                                    {{ $availableUser['name'] ?? '' }}, {{ $availableUser['vorname'] ?? '' }}
                                  </option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="form-group col-md-6">
                                <label for="forward_on">Weiterleitung von: &nbsp;<i class="fas fa-feather-alt fa-lg"
                                    style="color: #661421;"></i>
                                  &nbsp;<i class="fa-solid fa-right-from-bracket fa-lg" style="color:red;"></i></label>
                                <select class="form-control forward-user-select" name="forward_from" required>
                                  <option value="">Mitarbeiter</option>
                                  @foreach($users as $availableUser)
                                  <option value="{{ $availableUser['id'] }}" @if((int)($availableUser['id'] ??
                                    0)===(int)auth()->id()) selected @endif>
                                    {{ $availableUser['name'] ?? '' }}, {{ $availableUser['vorname'] ?? '' }}
                                  </option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="form-group mb-2 col-md-6">
                                <label for="forward_required_at">Benötigt ab</label>
                                <input type="text" class="form-control startdate" name="forward_required_at" required>
                              </div>
                              <div class="form-group mb-2 col-md-6">
                                <label for="forward_to_at">Bis</label>
                                <input type="text" class="form-control enddate" name="forward_to_at" required>
                                <small class="form-text text-primary">Zur Aufhebung ist kein Ticket erforderlich, die
                                  Weiterleitung wird nach dem angegebenen Datum entfernt.</small>

                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="form-group col-md-6">
                        </div>
                        <div class="form-group col-md-6 col-lg-12">
                          <label for="notizen">Beschreibung</label>
                          <textarea type="text" name="notizen" class="form-contro notizen"></textarea>
                        </div>
                        <div>
                          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                        </div>
                      </div>
                      <!-- ! Jquery forms ends here -->
                    </div>
                  </div>
                </div><!--end second card -->
              </div>
            </form>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div> <!-- /.col -->
    </div> <!-- /.row -->
  </div><!-- /.container-fluid -->
</section><!-- /.content -->



@endsection

@section('script')
<script>


  $(document).ready(function () {


    $(function () {
      moment.locale('de');
      $('.startdate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        startDate: moment(),
        minDate: moment(),
        minYear: parseInt(moment().format('YYYY')) - 1,
        maxYear: parseInt(moment().format('YYYY')) + 1,
        opens: 'center',
        locale: {
          format: 'DD-MM-YYYY'
        }
      });

      $('.enddate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        startDate: moment().add(1, 'day'),
        minDate: moment().add(1, 'day'),
        minYear: parseInt(moment().format('YYYY')) - 1,
        maxYear: parseInt(moment().format('YYYY')) + 1,
        opens: 'center',
        locale: {
          format: 'DD-MM-YYYY'
        }
      });

      $('.startdate').on('apply.daterangepicker', function (ev, picker) {
        const minEndDate = picker.startDate.clone().add(1, 'day');
        const endPicker = $('.enddate').data('daterangepicker');
        endPicker.minDate = minEndDate;
        if (endPicker.startDate.isBefore(minEndDate)) {
          endPicker.setStartDate(minEndDate);
          endPicker.setEndDate(minEndDate);
        }
      });

    });
    $('.forward-user-select').select2({
      width: '100%',
      placeholder: 'Mitarbeiter'
    });
    $('.notizen').summernote({
      height: 150,
      lang: 'de-DE'
    });

  });


</script>
@endsection