@extends('layouts.admin_layout.admin_layout')

@section('content')
@php
    // Retrieve the authenticated user.
    $user = auth()->user();

    // Prepare an array for the cards.
    $cards = [];

    // IT Tickets & Korso Tickets are visible by users with the "Verwaltung" role.
    if ($user->hasRole('Verwaltung')) {
        $cards[] = [
            'title' => 'IT Tickets',
            'url'   => route('it.tickets'), // Adjust this route name
            'icon'  => 'fas fa-desktop'      // Adjust icon class as needed
        ];
        $cards[] = [
            'title' => 'Korso Tickets',
            'url'   => route('korso.tickets'), // Adjust this route name
            'icon'  => 'fas fa-envelope'       // Adjust icon class as needed
        ];
    }

    // Handwerk Tickets are visible by users with the "handwerk" role.
    if ($user->hasRole('handwerk')) {
        $cards[] = [
            'title' => 'Handwerk Tickets',
            'url'   => route('handwerk.tickets'), // Adjust this route name
            'icon'  => 'fas fa-tools'             // Adjust icon class as needed
        ];
    }

    // Compute the Bootstrap column width based on how many cards to show.
    $cardsCount = count($cards);
    // If no cards found, you could redirect or show a message.
    $colWidth = $cardsCount > 0 ? (12 / $cardsCount) : 12;
@endphp

<div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 100px);">
    <div class="row w-100 justify-content-center">
        @foreach($cards as $card)
            <div class="col-md-{{ $colWidth }} mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="{{ $card['icon'] }} fa-3x mb-3"></i>
                        <h5 class="card-title">{{ $card['title'] }}</h5>
                        <a href="{{ $card['url'] }}" class="btn btn-primary">Ansehen</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('script')
<!-- Optional: custom script for landing page -->
@endsection