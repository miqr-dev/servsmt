@extends('layouts.admin_layout.admin_layout')

@section('content')
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

<div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: calc(100vh - 100px);">
    @if($activeForwarding)
        <div class="row w-100 justify-content-center mb-4">
            <div class="col-md-12">
                <div class="alert alert-info text-center shadow-sm" role="alert">
                    <i class="fas fa-info-circle mr-2"></i>
                    Für Ihr Konto ist bis zum <strong>{{ optional($activeForwarding->forward_to_at)->format('d.m.Y') }}</strong> eine E-Mail-Weiterleitung an „<strong>{{ optional($activeForwarding->forwardOnUser)->vorname }} {{ optional($activeForwarding->forwardOnUser)->name }}</strong>“ eingerichtet.
                </div>
            </div>
        </div>
    @endif

    @if($cityForwardings->isNotEmpty())
        <div class="row w-100 justify-content-center mb-5">
            <div class="col-md-10">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title text-bold"><i class="fas fa-mail-forward mr-2"></i> E-Mail-Weiterleitungen in {{ auth()->user()->ort }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Von</th>
                                    <th>An</th>
                                    <th>Bis zum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cityForwardings as $fwd)
                                    <tr>
                                        <td>{{ optional($fwd->forwardFromUser)->vorname }} {{ optional($fwd->forwardFromUser)->name }}</td>
                                        <td>{{ optional($fwd->forwardOnUser)->vorname }} {{ optional($fwd->forwardOnUser)->name }}</td>
                                        <td>{{ optional($fwd->forward_to_at)->format('d.m.Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
