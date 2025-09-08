@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Submitted Handwerks</h1>

    @foreach($handwerks as $handwerk)
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $handwerk->title }}</h5>  <!-- replace 'title' with your field name -->
            <p class="card-text">{{ $handwerk->description }}</p> <!-- replace 'description' with your field name -->
        </div>
    </div>
    @endforeach
</div>
@endsection

