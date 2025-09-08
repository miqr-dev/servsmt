@extends('layouts.admin_layout.admin_layout')
    
  <style>
      .container {
          color: #681a24 !important;
          font-family: 'Nunito', sans-serif;
          font-weight: 100;
          height: 100vh;
          overflow: auto;
          margin: 0;
      }

      .full-height {
          height: 100vh;
      }

      .flex-center {
          align-items: center;
          display: flex;
          justify-content: center;
      }

      .position-ref {
          position: relative;
      }

      .code {
          border-right: 2px solid;
          font-size: 26px;
          padding: 0 15px 0 15px;
          text-align: center;
      }

      .message {
          font-size: 18px;
          text-align: center;
      }
  </style>
@section('content')
  <div class="container">
    <div class="flex-center position-ref full-height">
      <div class="code">
          @yield('code')
      </div>

      <div class="message" style="padding: 10px;">
          @yield('message')
      </div>
    </div>
  </div>
@endsection
