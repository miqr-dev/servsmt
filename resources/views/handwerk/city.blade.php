@extends('layouts.admin_layout.admin_layout')

<style>
  .display-body {
    white-space: pre-wrap;
  }
</style>

@section('content')

<div class="container-fluid">
  <h2 class="text-center text-capitalize">{{ $city }} Handwerks</h2>
  <div class="text-right mb-3">
    <a href="{{ route('handwerk.city.openTicketsPDF', ['city' => $city]) }}" class="btn btn-danger">
      <i class="fas fa-file-pdf"></i> PDF Herunterladen
    </a>
  </div>
  <div class="row">

    <div class="col-md-6">
      <div class="row">
        <button class="btn btn-outline-primary  float-left ml-4" onclick="window.history.back(1);"><i
            class="fas fa-long-arrow-alt-left fa-lg"></i></button>
      </div>
      <div class="text-center p-3">
        <h3 class="d-inline-block mr-2">ToDo</h3>
        <span id="todo-count" class="btn btn-secondary"
          style="vertical-align: baseline; padding: .2rem .6rem; font-size: 1.1rem;"></span>
      </div>
      <div id="todo-list">
        @foreach ($todos as $todo)
        <div class="card mb-3" data-id="{{ $todo->id }}">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h5 class="display-title font-weight-bold">{{ $todo->title }}</h5>
                <input type="text" class="form-control edit-title d-none mb-2 w-100" value="{{ $todo->title }}">
                <p class="display-body">{{ $todo->body }}</p>
                <textarea class="form-control edit-body d-none w-100" rows="4">{{ $todo->body }}</textarea>
                <div class="d-flex justify-content-end align-items-center">
                  <p class="display-username float-right text-bold mr-2" style="color:#661421;">{{
                    $todo->submitter->username
                    }}</p>
                  <p class="display-updated_at float-right">{{ $todo->updated_at_german }}</p>

                </div>
              </div>
              <div>
                <button class="btn btn-primary btn-edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-success btn-save d-none ml-2"><i class="fas fa-save"></i></button>
                <button class="btn btn-success btn-done"><i class="fas fa-check"></i></button>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <div class="mt-3" id="todo-form">
        <h4>Neue Aufgabe hinzufügen</h4>
        <form id="add-todo-form">
          <div class="form-group">
            <label for="title">Aufgabentitel</label>
            <input class="form-control" id="title" required>
          </div>
          <div class="form-group">
            <label for="body">Aufgabenbeschreibung</label>
            <textarea class="form-control" id="body" rows="4" required></textarea>
          </div>
          <button class="btn btn-primary mt-2" type="submit">Neue Aufgabe?</button>
        </form>
      </div>
    </div>

    <div class="col-md-6">
      <!-- Right Side: Handwerk Table -->
      <h3 class="text-center">Handwerk Table</h3>
      <table class="display responsive compact table-sm" id="userhandwerkticket_tableCity">
        <thead>
          <tr>
            <th></th>
            <th>Anfrage</th>
            <th>Ersteller</th>
            <th>Standort</th>
            <th>Raum</th>
            <th class="text-right">Erstellt am</th>
            <th class="none">Beschreibung</th>
            <th class="text-right">Erledigt?</th>
          </tr>
        </thead>
        <tbody>
          @foreach($handwerks as $handwerk)
          <tr>
            <td></td> <!-- You can fill this as needed -->
            <td><a href="{{ route('handwerk_show', ['id' => $handwerk->id, 'from_city' => $city]) }}">{{ $handwerk->problem_type}}</a></td>
            <td>{{ $handwerk->submitter_name }}</td>
            <td>{{ @$handwerk->location->address}}</td>
            <td>{{ @$handwerk->room->rname }}</td>
            <td class="text-right"><span class="d-none">{{$handwerk->created_at->format('Ymd, hms')}}</span>
              {{$handwerk->created_at->diffForHumans()}}</td>
            <td>{!! @$handwerk->subject ? @$handwerk->subject : @$handwerk->notizen !!}</td>
            <td class="text-right">
              <button class="btn btn-success btn-outline btn-done-aufgabe" data-id="{{ $handwerk->id }}">
                <i class="fas fa-check"></i>
              </button>
            </td>

          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@section('script')
<script>
  var city = "{{ $city }}";
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function updateTodoCount() {
    let count = $('.card').length;
    $('#todo-count').text(`${count}`);
  }

  $(document).ready(function () {
    updateTodoCount();
    $('#userhandwerkticket_tableCity').DataTable({
      searching: true,
      pageLength: 10,
      paging: true,
      info: true,
      responsive: true,
      autoWidth: true,
      order: [5, 'desc'],
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/German.json"
      },
      columnDefs: [
        { "orderable": false, "targets": 4 },
      ]
    });

    $("#add-todo-form").on("submit", function (e) {
      e.preventDefault();

      let title = $("#title").val();
      let body = $("#body").val();

      $.ajax({
        url: "{{ route('handwerk.city.todos.store', ['city' => $city]) }}",
        method: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          title: title,
          body: body,
        },
        success: function (response) {
          // Clear the form
          $("#title").val("");
          $("#body").val("");

          // Add the new todo to the list
          let newTodo = `
<div class="card mb-3" data-id="${response.id}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h5 class="display-title font-weight-bold">${response.title}</h5>
                <input type="text" class="form-control edit-title d-none mb-2 w-100" value="${response.title}">
                <p class="display-body">${response.body}</p>
                <textarea class="form-control edit-body d-none w-100" rows="4">${response.body}</textarea>
                <div class="d-flex justify-content-end align-items-center">
                  <p class="display-username float-right text-bold mr-2" style="color:#661421;">${response.submitter.username}</p>
                  <p class="display-updated_at float-right">${response.updated_at_german}</p>
                </div>
            </div>
            <div>
                <button class="btn btn-primary btn-edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-success btn-save d-none ml-2"><i class="fas fa-save"></i></button>
                <button class="btn btn-success btn-done"><i class="fas fa-check"></i></button>
            </div>
        </div>
    </div>
</div>`;

          $("#todo-list").append(newTodo);
          updateTodoCount();

        },
        error: function (jqXHR, textStatus, errorThrown) {
          // Handle errors here
        }
      });
    });

    $(document).on("click", ".btn-edit", function (e) {
      let $card = $(this).closest('.card');
      $card.find('.display-title, .display-body, .btn-edit').addClass('d-none');
      $card.find('.edit-title, .edit-body, .btn-save').removeClass('d-none');
    });

    $(document).on("click", ".btn-save", function (e) {
      let $card = $(this).closest('.card');
      let todoId = $card.data('id');

      let title = $card.find('.edit-title').val();
      let body = $card.find('.edit-body').val();

      // Update the todo on the server
      $.ajax({
        url: `{{ url('/handwerker/') }}/${city}/todos/${todoId}`,
        method: 'post',
        data: {
          _token: "{{ csrf_token() }}",
          _method: 'PUT',
          title: title,
          body: body,
        },
        success: function (response) {
          // Update the todo on the list
          $card.find('.display-title').text(response.title).removeClass('d-none');
          $card.find('.display-body').text(response.body).removeClass('d-none');
          $card.find('.display-username').text(response.username).removeClass('d-none');
          $card.find('.display-updated_at').text(response.updated_at_german).removeClass('d-none');
          $card.find('.edit-title, .edit-body, .btn-save').addClass('d-none');
          $card.find('.btn-edit').removeClass('d-none');
        },
        error: function (jqXHR, textStatus, errorThrown) {
          // Handle errors here
        }
      });
    });

    $(document).on("click", ".btn-done-aufgabe", function () {
      let handwerkId = $(this).data('id'); // Get the ID of the ticket
      let row = $(this).closest('tr');    // Get the row for removal

      Swal.fire({
        title: 'Bist du sicher?',
        text: "Diese Aktion kann nicht rückgängig gemacht werden!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ja, erledigt!',
        cancelButtonText: 'Abbrechen'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('handwerk.ajaxDestroy', ':id') }}".replace(':id', handwerkId),
            method: "POST",
            data: {
              _token: "{{ csrf_token() }}"
            },
            success: function (response) {
              if (response.success) {
                Swal.fire('Erledigt!', response.message, 'success');
                row.remove(); // Remove the row from the table
              } else {
                Swal.fire('Fehler!', response.message, 'error');
              }
            },
            error: function (jqXHR) {
              Swal.fire('Fehler!', jqXHR.responseJSON.message || 'Ein Fehler ist aufgetreten.', 'error');
            }
          });
        }
      });
    });

    $(document).on("click", ".btn-done", function (e) {
      let todoId = $(this).closest('.card').data('id');
      let card = $(this).closest('.card');

      Swal.fire({
        title: 'Bist du sicher?',
        text: "Du kannst dies nicht rückgängig machen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ja, löschen!',
        cancelButtonText: 'Aufheben'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ url('handwerker') }}/" + city + "/todos/" + todoId,
            method: "POST",
            data: {
              _token: "{{ csrf_token() }}",
              _method: 'DELETE'
            },
            success: function (response) {
              Swal.fire(
                'Gelöscht!',
              )
              // Remove the todo from the list
              card.remove();
              updateTodoCount();
            },
            error: function (jqXHR, textStatus, errorThrown) {
              // Handle errors here
            }
          });
        }
      })

    });
  });
</script>
@endsection