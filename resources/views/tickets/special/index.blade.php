@extends('layouts.admin_layout.admin_layout')

<style>
  [x-cloak] {
    display: none !important;
  }

  .clickable-row {
    cursor: pointer;
  }

  .city-btn {
    min-width: 130px;
  }

  .ticket-desc {
    max-height: 5.5rem;
    overflow: hidden;
  }
</style>

@php
$cityCounts = [];
foreach ($cities as $c) { $cityCounts[$c] = 0; }
$totalCount = 0;
foreach ($tickets as $t) {
$c = optional($t->subUser)->ort;
if ($c && array_key_exists($c, $cityCounts)) { $cityCounts[$c]++; }
$totalCount++;
}

$canEditSpecial = auth()->check() && (auth()->id() === 16 || auth()->user()->hasRole('Super_Admin'));
@endphp

@section('content')
<section class="content"
  x-data="{ selectedCity: '', cities: {{ json_encode($cities, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) }} }">
  <div class="container-fluid">
    <div class="card card-primary card-outline">

      <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        <h3 class="card-title mb-2"></h3>

        {{-- City filter buttons --}}
        <div class="d-flex justify-content-between flex-wrap" style="gap:.5rem; min-width: 320px;">
          <button type="button" class="btn btn-sm city-btn mb-2"
            :class="selectedCity === '' ? 'btn-primary' : 'btn-outline-secondary'" @click="selectedCity = ''">
            Alle | {{ $totalCount }}
          </button>
          @foreach($cities as $city)
          <button type="button" class="btn btn-sm city-btn mb-2"
            :class="selectedCity === {{ json_encode($city) }} ? 'btn-primary' : 'btn-outline-secondary'"
            @click="selectedCity = {{ json_encode($city) }}">
            {{ $city }} | {{ $cityCounts[$city] ?? 0 }}
          </button>
          @endforeach
        </div>
      </div>

      <div class="card-body">
        {{-- Card grid --}}
        <div class="row" id="ticketsGrid">
          @forelse($tickets as $ticket)
          @php
          $city = optional($ticket->subUser)->ort ?? '';
          $username = optional($ticket->subUser)->username ?? '—';
          $device = optional($ticket->invitem)->gname ?? '—';
          $status = (int)($ticket->ticket_status_id ?? 0);
          $priority = (int)($ticket->priority_id ?? 0);
          @endphp

          <div class="col-sm-12 col-md-6 col-lg-4 mb-3"
            x-show="selectedCity === '' || selectedCity === {{ json_encode($city) }}" x-cloak>
            <div class="card h-100 shadow-sm">
              <div class="card-body clickable-row" data-href="{{ url('ticket/'.$ticket->id) }}">
                {{-- Header row --}}
                <div class="d-flex align-items-start justify-content-between">
                  <div class="d-flex align-items-center">
                    @if($status === 1)
                    <i class="fas fa-circle mr-2" data-toggle="tooltip" title="Nicht begonnen"
                      style="color:#001B2E"></i>
                    @elseif($status === 2)
                    <i class="fas fa-wrench mr-2" data-toggle="tooltip" title="In Bearbeitung"
                      style="color:#3490DC"></i>
                    @elseif($status === 3)
                    <i class="far fa-check-circle mr-2" data-toggle="tooltip" title="Erledigt"
                      style="color:#285D17"></i>
                    @elseif($status === 4)
                    <i class="fas fa-user-friends mr-2" data-toggle="tooltip" title="Wartet auf jemand anderen"
                      style="color:#F9A620"></i>
                    @elseif($status === 5)
                    <i class="fas fa-pause mr-2" data-toggle="tooltip" title="Zurückgestellt" style="color:#e3342f"></i>
                    @elseif($status === 7)
                    <i class="fa-solid fa-message mr-2" data-toggle="tooltip" title="Warten auf Antwort"
                      style="color:#c2410c"></i>
                    @else
                    <i class="far fa-copy mr-2" data-toggle="tooltip" title="Duplikat" style="color:#285D17"></i>
                    @endif

                    <h5 class="mb-0">{{ $ticket->problem_type }}</h5>
                  </div>
                  <div>
                    @if($priority === 1)
                    <span class="badge badge-pill badge-primary">Niedrig</span>
                    @elseif($priority === 2)
                    <span class="badge badge-pill badge-success">Normal</span>
                    @else
                    <span class="badge badge-pill badge-danger">Hoch</span>
                    @endif
                  </div>
                </div>

                {{-- Device --}}
                <div class="mt-2">
                  <strong>Gerät:</strong> {{ $device }}
                </div>

                {{-- Description --}}
                @if(!empty($ticket->notizen))
                <div class="mt-2 ticket-desc">
                  {!! $ticket->notizen !!}
                </div>
                @endif
              </div>

              {{-- Footer meta --}}
              <div class="card-footer d-flex justify-content-between align-items-center">
                <small>
                  Von: {{ $username }}
                  @if($city) · {{ $city }} @endif
                </small>
                <small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small>
              </div>

              {{-- SPECIAL COMMENT editor/view — UNDER the footer --}}
              {{-- SPECIAL COMMENTS (multiple) --}}
              <div class="card-footer border-top sc-area" data-ticket="{{ $ticket->id }}">
                <div class="d-flex align-items-center mb-2">
                  <strong class="mr-2">Kommentare</strong>
                  <small class="text-muted" id="sc-count-{{ $ticket->id }}">{{ $ticket->specialComments->count()
                    }}</small>
                </div>

                {{-- list --}}
                <ul class="list-unstyled mb-2" id="sc-list-{{ $ticket->id }}">
                  @foreach($ticket->specialComments as $c)
                  <li class="media mb-2" id="sc-item-{{ $c->id }}">
                    <div class="media-body">
                      <div class="d-flex justify-content-between">
                        <div>
                          <strong>{{ optional($c->author)->name ?? optional($c->author)->username ??
                            ('User#'.$c->user_id) }}</strong>
                          <small class="text-muted">· {{ $c->created_at->diffForHumans() }}</small>
                        </div>
                        @php
                        $currentUserId = auth()->id();
                        @endphp
                        @if($canEditSpecial && $currentUserId === $c->user_id)
                        <div>
                          <button class="btn btn-link btn-sm p-0 mr-2"
                            onclick="scEnterEdit({{ $ticket->id }}, {{ $c->id }})">Bearbeiten</button>
                          <button class="btn btn-link btn-sm text-danger p-0"
                            onclick="scDelete({{ $ticket->id }}, {{ $c->id }})">Löschen</button>
                        </div>
                        @endif
                      </div>
                      <div class="mt-1" id="sc-body-{{ $c->id }}">{{ $c->body }}</div>

                      {{-- inline editor (hidden by default) --}}
                      @if($canEditSpecial)
                      <div class="mt-2 d-none" id="sc-editbox-{{ $c->id }}">
                        <textarea class="form-control form-control-sm mb-2" id="sc-editarea-{{ $c->id }}"
                          rows="2">{{ $c->body }}</textarea>
                        <button class="btn btn-sm btn-success mr-2"
                          onclick="scSaveEdit({{ $ticket->id }}, {{ $c->id }})">Speichern</button>
                        <button class="btn btn-sm btn-outline-secondary"
                          onclick="scCancelEdit({{ $c->id }})">Abbrechen</button>
                      </div>
                      @endif
                    </div>
                  </li>
                  @endforeach
                </ul>

                {{-- add new --}}
                @if($canEditSpecial)
                <div class="mt-2">
                  <textarea class="form-control form-control-sm mb-2" id="sc-new-{{ $ticket->id }}" rows="2"
                    placeholder="Neuen Kommentar schreiben…"></textarea>
                  <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-primary mr-2" onclick="scAdd({{ $ticket->id }})">Hinzufügen</button>
                    <small class="text-muted" id="sc-status-{{ $ticket->id }}"></small>
                  </div>
                </div>
                @endif
              </div>
            </div>
          </div>
          @empty
          <div class="col-12 text-center">
            <img src="/images/admin_images/no_ticket.png" alt="Kein Ticket">
          </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</section>
@endsection

@section('script')
<script>
  window.CURRENT_USER_ID = {{ auth() -> id() ?? 'null' }};

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'X-Requested-With': 'XMLHttpRequest'
    },
    xhrFields: { withCredentials: true }
  });
  function scSetStatus(ticketId, msg, ok = true) {
    const el = document.getElementById('sc-status-' + ticketId);
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('text-danger', 'text-success', 'text-muted');
    el.classList.add(ok ? 'text-success' : 'text-danger');
    setTimeout(() => {
      el.classList.remove('text-danger', 'text-success');
      el.classList.add('text-muted');
    }, 2000);
  }

  function scUpdateCount(ticketId, delta) {
    const el = document.getElementById('sc-count-' + ticketId);
    if (!el) return;
    const num = parseInt(el.textContent || '0', 10) + delta;
    el.textContent = num < 0 ? 0 : num;
  }

  // CREATE
  function scAdd(ticketId) {
  const ta = document.getElementById('sc-new-' + ticketId);
  if (!ta) return;
  const body = (ta.value || '').trim();
  if (!body.length) { scSetStatus(ticketId, 'Leer', false); return; }

  $.post('/ticket/' + ticketId + '/special-comments', { body }, function(res){
    if (res && res.ok && res.comment) {
      const c = res.comment;

      const canEditThis = (window.CURRENT_USER_ID && c.author_id === window.CURRENT_USER_ID);
      const buttonsHtml = canEditThis
        ? '<button class="btn btn-link btn-sm p-0 mr-2" onclick="scEnterEdit(' + ticketId + ',' + c.id + ')">Bearbeiten</button>' +
          '<button class="btn btn-link btn-sm text-danger p-0" onclick="scDelete(' + ticketId + ',' + c.id + ')">Löschen</button>'
        : '';

      const li = document.createElement('li');
      li.className = 'media mb-2';
      li.id = 'sc-item-' + c.id;
      li.innerHTML =
        '<div class="media-body">' +
          '<div class="d-flex justify-content-between">' +
            '<div><strong>' + escapeHtml(c.author) + '</strong> ' +
            '<small class="text-muted">· ' + escapeHtml(c.created_human || '') + '</small></div>' +
            '<div>' + buttonsHtml + '</div>' +
          '</div>' +
          '<div class="mt-1" id="sc-body-' + c.id + '">' + escapeHtml(c.body) + '</div>' +
          '<div class="mt-2 d-none" id="sc-editbox-' + c.id + '">' +
            '<textarea class="form-control form-control-sm mb-2" id="sc-editarea-' + c.id + '" rows="2">' + escapeHtml(c.body) + '</textarea>' +
            '<button class="btn btn-sm btn-success mr-2" onclick="scSaveEdit(' + ticketId + ',' + c.id + ')">Speichern</button>' +
            '<button class="btn btn-sm btn-outline-secondary" onclick="scCancelEdit(' + c.id + ')">Abbrechen</button>' +
          '</div>' +
        '</div>';

      document.getElementById('sc-list-' + ticketId).prepend(li);
      ta.value = '';
      scUpdateCount(ticketId, +1);
      scSetStatus(ticketId, 'Hinzugefügt', true);
    } else {
      scSetStatus(ticketId, (res && res.message) ? res.message : 'Fehler', false);
    }
  }).fail(function(xhr){
    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Fehler';
    scSetStatus(ticketId, msg, false);
  });
}

  // EDIT UI
  function scEnterEdit(ticketId, commentId) {
    $('#sc-editbox-' + commentId).removeClass('d-none');
  }
  function scCancelEdit(commentId) {
    $('#sc-editbox-' + commentId).addClass('d-none');
  }

  // UPDATE
  function scSaveEdit(ticketId, commentId) {
    const ta = document.getElementById('sc-editarea-' + commentId);
    if (!ta) return;
    const body = (ta.value || '').trim();
    if (!body.length) { scSetStatus(ticketId, 'Leer', false); return; }

    $.post('/ticket/' + ticketId + '/special-comments/' + commentId + '/update', { body }, function (res) {
      if (res && res.ok && res.comment) {
        $('#sc-body-' + commentId).text(res.comment.body || '');
        $('#sc-editbox-' + commentId).addClass('d-none');
        scSetStatus(ticketId, 'Gespeichert', true);
      } else {
        scSetStatus(ticketId, (res && res.message) ? res.message : 'Fehler', false);
      }
    }).fail(function (xhr) {
      const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Fehler';
      scSetStatus(ticketId, msg, false);
    });
  }

  // DELETE
  function scDelete(ticketId, commentId) {
    if (!confirm('Kommentar wirklich löschen?')) return;
    $.post('/ticket/' + ticketId + '/special-comments/' + commentId + '/delete', {}, function (res) {
      if (res && res.ok) {
        $('#sc-item-' + commentId).remove();
        scUpdateCount(ticketId, -1);
        scSetStatus(ticketId, 'Gelöscht', true);
      } else {
        scSetStatus(ticketId, (res && res.message) ? res.message : 'Fehler', false);
      }
    }).fail(function (xhr) {
      const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Fehler';
      scSetStatus(ticketId, msg, false);
    });
  }


  // tiny escape helper to avoid injecting HTML
  function escapeHtml(str) {
    return (str || '').replace(/[&<>"']/g, function (m) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]);
    });
  }
</script>

@endsection