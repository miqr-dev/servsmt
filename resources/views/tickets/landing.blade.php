@extends('layouts.admin_layout.admin_layout')

<style>
.bg-handwerk {
  background-color: #007bff !important; /* or any blue shade you prefer */
}

.bg-korso {
  background-color: #65A30D !important;
}
.card-landing {
  min-height: 250px; /* Adjust this value to your desired height */
}
</style>

@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 100px);">
    <div class="row w-100 justify-content-center">
        @foreach($cards as $card)
            <div class="col-md-{{ $colWidth }} mb-4">
                <a href="{{ $card['url'] }}" class="text-decoration-none">
                    <div class="card h-100 card-landing">
                        <!-- Card body with custom background -->
                        <div class="card-body d-flex flex-column justify-content-center align-items-center bg-{{ $card['color'] }} text-white">
                            <h5 class="card-title">{{ $card['title'] }}</h5>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('script')
<!-- Optional page-specific JS -->
@endsection
