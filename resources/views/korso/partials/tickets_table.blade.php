@foreach($tickets as $ticket)
<tr id="ticket-{{ $ticket->id }}" class="{{ $ticket->priority == 3 ? 'table-danger' : '' }}">
  <td> {{ $ticket->id }}</td>
  <td>
    <a href="{{ url('/korso/' . $ticket->id) }}" class="text-primary font-weight-bold">
      {{ strtoupper(substr($ticket->subUser->vorname, 0, 1)) }}. {{ $ticket->subUser->name }}
    </a>
  </td>
  <td>
    @if($ticket->priority == 3)
    <span class="badge badge-danger">Hoch</span>
    @elseif($ticket->priority == 2)
    <span class="badge badge-warning">Normal</span>
    @else
    <span class="badge badge-secondary">Niedrig</span>
    @endif
  </td>
  <td>{{ $ticket->problem_type }}</td>
  <td>
    @if($ticket->ticket_status)
    @php
    $statusColors = [
    1 => 'secondary', // Nicht begonnen
    2 => 'warning', // In Bearbeitung
    3 => 'success', // Erledigt
    4 => 'info', // Wartet auf jemand anderen
    5 => 'dark', // Zurückgestellt
    6 => 'danger', // Duplikat
    7 => 'primary', // Warten auf Antwort
    8 => 'warning', // Wiederhergestellt
    ];
    $badgeClass = $statusColors[$ticket->ticket_status->id] ?? 'light';
    @endphp
    <span class="badge badge-{{ $badgeClass }}">{{ $ticket->ticket_status->name }}</span>
    @else
    <span class="badge badge-secondary">nicht zugewiesen</span>
    @endif
  </td>
  <td>
    <select class="assign-user form-control" data-id="{{ $ticket->id }}">
      <option value="">nicht zugewiesen</option>
      @foreach($korso_ma_users as $korso_ma)
      <option value="{{ $korso_ma->id }}" {{ $ticket->assignedTo == $korso_ma->id ? 'selected' : '' }}>
        {{ strtoupper(substr($korso_ma->vorname, 0, 1)) }}. {{ $korso_ma->name }}
      </option>
      @endforeach
    </select>
  </td>

  <td data-sort="{{ $ticket->created_at }}">{{ $ticket->created_at->diffForHumans() }}</td>
  <td>
    <!-- Mark as Done -->
    <button class="btn btn-sm mark-done" data-id="{{ $ticket->id }}"
      style="color:#65A30D; border:none; background:none;">
      <i class="fas fa-check-circle fa-xl"></i>
    </button>

    <!-- View Details -->
    <button class="btn btn-sm view-details" data-id="{{ $ticket->id }}"
      style="color:#007bff; border:none; background:none;">
      <i class="fas fa-eye fa-xl"></i>
    </button>
  </td>
</tr>
@endforeach