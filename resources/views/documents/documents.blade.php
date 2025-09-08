@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container">
    <h2>Documents for {{ $bundesland }}</h2>
    <form action="{{ url('/documents/edit') }}" method="post">
        @csrf
        <div class="form-check">
            @foreach($documents as $document)
                <input class="form-check-input" type="checkbox" name="documents[]" value="{{ $document }}">
                <label class="form-check-label">{{ $document }}</label><br>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary">Next</button>
    </form>
</div>
@endsection
