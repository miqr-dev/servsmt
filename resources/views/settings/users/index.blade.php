@extends('layouts.admin_layout.admin_layout')

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <hr>
        <table id="UserdataTable" class="display" style="width:100%">
          <thead>
              <tr>
                  <th>Name</th>
                  <th>BenutzerName</th>
                  <th>Position</th>
                  <th>Abteilung</th>
                  <th>Email</th>
                  <th>Bundesland</th>
              </tr>
          </thead>
          <tbody>
            @foreach($users as $user)
              <tr>
                  <td>{{$user->name}}</td>
                  <td>{{$user->username}}</td>
                  <td>{{$user->position}}</td>
                  <td>{{$user->abteilung}}</td>
                  <td>{{$user->email}}</td>
                  <td>{{$user->bundesland}}</td>
              </tr>
            @endforeach 
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

@endsection



@section('script')
<script>

$(document).ready(function() {
    $('#UserdataTable').DataTable( {
        "order": [[ 3, "desc" ]]
    } );
} );

</script>

@endsection