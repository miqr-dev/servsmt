@extends('layouts.admin_layout.admin_layout')

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <hr>
        <table id="machinedataTableAll" class="display" style="width:100%">
          <thead>
            <tr>
              <th>Geräte Name</th>
              <th>Geräte Art</th>
              <th>Adresse</th>
              <th>Raum</th>
              <th>Altr.Name</th>
              <th>Land</th>
              <th>AusDat</th>
            </tr>
          </thead>
          <tbody>
            @foreach($machines as $machine)
            <tr>
              <td>{{@$machine->gname}}</td>
              <td>{{@$machine->garts->name}}</td>
              <td>{{@$machine->invroom->location->address}}</td>
              <td>{{@$machine->invroom->rname}}</td>
              <td>{{@$machine->invroom->altrname}}</td>
              <td>{{@$machine->invroom->location->place->pnname}}</td>
              <td>{{@$machine->ausdat}}</td>
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
   $('#machinedataTableAll').DataTable({
    } );
} );

</script>

@endsection