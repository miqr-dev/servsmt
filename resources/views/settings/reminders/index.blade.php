@extends('layouts.admin_layout.admin_layout')
@section('content')

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="container" x-data>
    <div class="row">
        <div class="col-md-12">
            <h2>Reminders</h2>
            <a class="btn btn-success" href="{{ route('reminders.create') }}" style="margin-bottom: 20px;">Create New Reminder</a>
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    {{ $message }}
                </div>
            @endif
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ticket ID</th>
                        <th>Reminder Date</th>
                        <th>Is Reminded</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($reminders as $reminder)
                    <tr>
                        <td>{{ $reminder->id }}</td>
                        <td>{{ $reminder->ticket_id }}</td>
                        <td>{{ $reminder->reminder_date }}</td>
<td>
    <div x-data="{ isReminded: {{ $reminder->is_reminded ? 'true' : 'false' }} }">
        <button :class="isReminded ? 'btn btn-success' : 'btn btn-danger'"
                @click="isReminded = !isReminded; $nextTick(() => toggleRemind({{ $reminder->id }}, isReminded))">
            <span x-show="isReminded">Yes</span>
            <span x-show="!isReminded">No</span>
        </button>
    </div>
</td>
                        <td>{{ $reminder->user ? $reminder->user->name : 'N/A' }}</td>
                        <td>
                            <a class="btn btn-info" href="{{ route('reminders.show', $reminder->id) }}">Show</a>
                            <a class="btn btn-primary" href="{{ route('reminders.edit', $reminder->id) }}">Edit</a>
                            <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

  </div>
</section>


@include('settings.modal_settings.roles.roles_view')
@endsection

@section('script')
<script>
function toggleRemind(reminderId, status) {
    fetch(`/reminders/${reminderId}/toggle-remind`, {
        method: 'POST', // Change this to POST
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            isReminded: status,
            _method: 'PATCH' // Laravel will treat this as a PATCH request
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Success:', data);
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}
</script>
@endsection