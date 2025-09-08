<div class="p-3">
  <h5 class="text-success font-weight-bold">Ticket Details</h5>
  <hr>

  <p><strong>Ersteller:</strong> {{ $ticket->subUser->name ?? 'Unbekannt' }}</p>
  <p><strong>Priorität:</strong>
    @if($ticket->priority == 3)
    <span class="badge badge-danger">Hoch</span>
    @elseif($ticket->priority == 2)
    <span class="badge badge-warning">Normal</span>
    @else
    <span class="badge badge-secondary">Niedrig</span>
    @endif
  </p>

  <p><strong>Typ:</strong> {{ $ticket->problem_type }}</p>

  @if($ticket->problem_type === 'Onlinemarketing' && $ticket->onlinemarketingItem)
  <p><strong>Online Marketing Item:</strong> {{ $ticket->onlinemarketingItem->name }}</p>
  @elseif($ticket->problem_type === 'Zertifizierung & Qualitätsmanagement' && $ticket->zertifizierungItem)
  <p><strong>Zertifizierung:</strong> {{ $ticket->zertifizierungItem->name }}</p>
  @endif

  @if($ticket->massnahme)
  <p><strong>Maßnahme:</strong> {{ $ticket->massnahme->name }}</p>
  @endif

  <p><strong>Status:</strong> {{ $ticket->ticket_status->name ?? 'Nicht begonnen' }}</p>
  <p><strong>Zugewiesen an:</strong> {{ $ticket->assignedUser->name ?? 'Nicht zugewiesen' }}</p>
  <p><strong>Erstellt am:</strong> {{ $ticket->created_at->format('d.m.Y H:i') }}</p>

  <hr>
  <h6><i class="fas fa-clipboard"></i> Beschreibung</h6>
  <p>{!! $ticket->notizen ?? '<em>Keine Beschreibung verfügbar.</em>' !!}</p>

  <h6><i class="fas fa-map-marker-alt"></i> Standort</h6>
  <p>{{ optional($ticket->location)->address ?? '-' }}</p>

  @if($ticket->kcourses->isNotEmpty())
  <hr>
  <h6><i class="fas fa-box"></i> Bestellung Flyer</h6>
  <ul class="pl-3">
    @foreach($ticket->kcourses as $kcourse)
    <li>
      <strong>{{ $kcourse->payer->name }}</strong> → {{ $kcourse->name }}
      <span class="badge badge-success ml-2">{{ $kcourse->pivot->quantity }}</span>
    </li>
    @endforeach
  </ul>
  @endif

  @if($ticket->korsoItems->isNotEmpty())
  <hr>
  <h6><i class="fas fa-shopping-cart"></i> Bestellte Artikel</h6>
  <ul class="pl-3">
    @foreach($ticket->korsoItems as $item)
    <li class="mb-2">
      {{ $item->item_name }}
      <span class="badge badge-success ml-2">{{ $item->quantity }}</span>

      @if($item->item_name === 'Visitenkarten' && $item->details)
      @php
      $details = json_decode($item->details, true);
      @endphp
      <ul class="list-unstyled mt-2 ml-3 small text-muted">
        @if(!empty($details['name'])) <li><strong>Name:</strong> {{ $details['name'] }}</li> @endif
        @if(!empty($details['email'])) <li><strong>Email:</strong> {{ $details['email'] }}</li> @endif
        @if(!empty($details['telephone'])) <li><strong>Telefon:</strong> {{ $details['telephone'] }}</li> @endif
        @if(!empty($details['mobile'])) <li><strong>Mobil:</strong> {{ $details['mobile'] }}</li> @endif
        @if(!empty($details['fax'])) <li><strong>Fax:</strong> {{ $details['fax'] }}</li> @endif
      </ul>
      @endif
    </li>
    @endforeach
  </ul>
  @endif

  @if($ticket->korsoAttachments->isNotEmpty())
  <hr>
  <h6><i class="fas fa-paperclip"></i> Anhänge</h6>
  <div class="d-flex flex-wrap">
    @foreach($ticket->korsoAttachments as $attachment)
    @if(str_contains($attachment->file_type, 'image'))
    <div class="p-1">
      <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
        <img src="{{ asset('storage/' . $attachment->file_path) }}" class="img-thumbnail" width="80">
      </a>
    </div>
    @elseif($attachment->file_type === 'application/pdf')
    <div class="p-1">
      <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
        <i class="fas fa-file-pdf fa-3x text-danger"></i>
      </a>
    </div>
    @endif
    @endforeach
  </div>
  @endif
</div>