@extends('layouts.admin_layout.admin_layout')

<style>
.center {
position: absolute;
left: 55%;
top: 50%;
transform: translate(-50%, -50%);
padding: 10px;
}
</style>
 
@section('content')
<div class="center">
  <img src="/images/admin_images/404_whiteman.png"  style="max-width:300px;" alt="" />
  <br>
  <br>
  <h5 class="text-center" style="color: #661421;"><strong>Oh nein.....diese Seite haben wir leider nicht gefunden</strong></h5>
  <p class="float-left" style="color: #661421;"><strong>Wahrscheinlich wurde das Ticket von dem Benutzer gelöscht, der es erstellt hat.</strong></p>
</div>
@endsection

@section('script')

@endsection








404_whiteman