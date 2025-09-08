@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container">
    <h2>Select Bundesland</h2>
    <div class="row">
        @foreach($bundeslaender as $bundesland)
            <div class="col-4">
                <a href="{{ url('/documents/variables/' . $bundesland) }}" class="btn btn-primary btn-block">{{ $bundesland }}</a>
            </div>
        @endforeach
    </div>
</div>
@endsection
