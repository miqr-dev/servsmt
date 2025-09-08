@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container">
    <h2>Edit Variables for {{ $bundesland }}</h2>

    <form action="{{ url('/documents/update') }}" method="post">
        @csrf
        <input type="hidden" name="bundesland" value="{{ $bundesland }}">
        <div class="row">
            @foreach($variables as $variable => $value)
                <div class="form-group col-6">
                    <label for="{{ $variable }}">{{ $variable }}</label>
                    @if(in_array($variable, ['tagesaktuellesDatum', 'maßnahmebeginn', 'maßnahmeende', 'praktikumsbeginn', 'praktikumsende', 'spezialisierungStart', 'spezialisierungEnd','hol_beginn','hol_ende','hol1_beginn','hol1_ende','hol2_beginn','hol2_ende']))
                        <input type="text" class="form-control datepicker" name="{{ $variable }}" id="{{ $variable }}" value="{{ $value }}">
                    @else
                        <input type="text" class="form-control" name="{{ $variable }}" value="{{ $value }}">
                    @endif
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-success">Save and Generate Links</button>
    </form>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.datepicker').daterangepicker({
        singleDatePicker: true,
        autoUpdateInput: false,
        locale: {
            format: 'DD.MM.YYYY',
            cancelLabel: 'Clear'
        }
    });

    $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY'));
    });

    $('.datepicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endsection
