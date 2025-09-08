<style>
  .datefont {
  color: #661421;
  font-size: 20;
  font-family: "Abel", sans-serif;
}
</style>
<span class="mx-auto mb-2 datefont">{{($ticket->participant_required_at) ? $ticket->formatted_participant_required_at : ''}}</span>
<div class="table-responsive">
<table class="table table-sm text-small" id="participant_table" >
  <thead>
    <tr>
      <th scope="col">Vorname</th>
      <th scope="col">Nachname</th>
      <th scope="col">Benutzername</th>
      <th scope="col">Passwort</th>
      <th scope="col">Maßnahme</th>
      <th scope="col">Email</th>
      <th scope="col">Standort</th>
      <th scope="col">Bemerkung</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    @foreach($teilnehmers as $teilnehmer)
    <tr>
      <td>{{$teilnehmer->vorname}}</td>
      <td>{{$teilnehmer->nachname}}</td>
      @if(auth()->user()->hasRole('Super_Admin'))
      <td><input class="form-control password" type="text" name="username" id="{{$teilnehmer->id}}" 
                  value="{{$teilnehmer->username ? $teilnehmer->username : ''}}"></td>
      @else 
      <td>{{@$teilnehmer->username}}</td>
      @endif
      <td style="font-family: 'Courier Prime', monospace;">{{$teilnehmer->password}}</td>
      <td>{{$teilnehmer->course}}</td>
      <td>{{$teilnehmer->email}}</td>
      <td>{{@$teilnehmer->location}}</td>
      <td>{{$teilnehmer->notes_participant}}</td>
      <form action="{{ route('participants.destroy',$teilnehmer->id) }}" method="POST" id="delete-participant-form">
        @csrf
        @method('DELETE')
        <td><button type="submit" class="btn show_confirm_participant"><i class="far fa-trash-alt" style="color:#e3342f;"></i></button></td>
        </form>
    </tr>
    @endforeach
  </tbody>
</table>
</div>



