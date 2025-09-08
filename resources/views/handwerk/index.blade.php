@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header',['title'=>'Handwerkaufgaben'])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
        <div class="card card-secondary card-outline" id="content">
          <div class="card-body box-profile form-group">
            <div class="row mx-auto justify-content-center">
              <div class="col-lg-12">
                <div class="row d-flex justify-content-around">
                  @if(auth()->user()->hasAnyRole('Super_Admin','handwerk_admin'))
                  @foreach($cityCounts as $city => $count)
                  <div>
                    <a href="{{ route('handwerk.city', ['city' => str_replace('Döbeln', 'doebeln', mb_strtolower($city, 'UTF-8'))]) }}"
                      class="city-link"
                      style="display: inline-block; padding: 10px 20px; background-color: #f4c900; color: #000; text-decoration: none; border: 1px solid #000; border-radius: 4px; font-weight: bold;">
                      {{ $city }}
                    </a>
                    <span class="badge badge-success" style="font-size:100%;background-color:#004873">{{ $count
                      }}</span>
                  </div>
                  @endforeach
                  @endif


                </div>
              </div>
            </div>
          </div>
          <div class="container-fluid">
            <div class="card-deck">
              <div class="row mx-auto justify-content-center">
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header text-center font-weight-bold" style="color:#004873;">
                    Neu - Standort
                  </div>
                  <ul class="list-group list-group-flush">
                    <div class="card-body list-group-item">
                      <a href="{{route('neustandort')}}" class="text-decoration-none">
                        <h6 class="text-center" style="color: #0284c7;">Neu Standort vorbereitung</h5>
                      </a>
                    </div>
                  </ul>
                </div>
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header text-center font-weight-bold" style="color:#004873;">
                    Mobiliar - Einrichtung
                  </div>
                  <ul class="list-group list-group-flush">
                    <div class="card-body list-group-item">
                      <a href="{{route('einrichtungsgegenstände')}}" class="text-decoration-none">
                        <h6 class="text-center" style="color: #0284c7;">Mobiliar</h6>
                      </a>
                    </div>
                    <div class="card-body list-group-item">
                      <a href="{{route('elektro')}}" class="text-decoration-none">
                        <h6 class="text-center" style="color: #0284c7;">Elektro</h6>
                      </a>
                    </div>
                  </ul>
                </div>
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header text-center font-weight-bold" style="color:#004873;">
                    Reparatur
                  </div>
                  <ul class="list-group list-group-flush">
                    <div class="card-body list-group-item">
                      <a href="{{route('reparatur_mobiliar')}}" class="text-decoration-none">
                        <h6 class="text-center" style="color: #0284c7;">Mobiliar</h6>
                      </a>
                    </div>
                    <div class="card-body list-group-item">
                      <a href="{{route('reparatur_elektro')}}" class="text-decoration-none">
                        <h6 class="text-center" style="color: #0284c7;">Elektro</h6>
                      </a>
                    </div>
                  </ul>
                </div>
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header text-center font-weight-bold" style="color:#004873;">
                    Modifikation / Bauliche Veränderungen
                  </div>
                  <ul class="list-group list-group-flush">
                    <div class="card-body list-group-item">
                      <a href="{{route('modifikation')}}" class="text-decoration-none">
                        <h6 class="text-center" style="color: #0284c7;">Modifikation</h6>
                      </a>
                    </div>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- end row -->
  </div>
</section>
  @endsection
  
   @section('script')

  <script>

  </script>
  @endsection
  