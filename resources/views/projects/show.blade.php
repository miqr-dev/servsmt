@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
  <div class="bg-white shadow sm:rounded-lg p-6">
    <div class="flex flex-col sm:flex-row justify-between items-center">
      <h2 class="text-2xl font-semibold leading-tight text-gray-800 mb-2 sm:mb-0">{{ $project->name }}</h2>
      <span class="px-3 py-1 text-sm font-semibold text-green-800 bg-green-200 rounded-full">
        {{ $project->status }}
      </span>
    </div>

    <!-- Project Details Section -->
    <div class="text-gray-700 mt-1">
      {{ $project->description }}
    </div>

    <!-- Attach and Attached Tickets Section -->
    <div class="grid grid-cols-2 gap-4 mt-6">
      <div>
        <h4 class="text-lg font-semibold text-gray-700">Attach Tickets</h4>
        <form action="{{ route('projects.attach-tickets', ['project' => $project->id]) }}" method="POST" class="mt-4">
          @csrf
          <select name="ticket_ids[]" multiple class="select2-multi w-full text-sm">
            @foreach ($tickets as $ticket)
            <option value="{{ $ticket->id }}" title="{!! strip_tags($ticket->notizen) !!}">{{ $ticket->problem_type }} -
              {{ $ticket->subUser->vorname ?? 'N/A' }}
              {{ $ticket->subUser->name ?? 'N/A' }},
              {{ @$ticket->subUser->straße }} - {{ @$ticket->location->address}}</option>
            @endforeach
          </select>
          <button type="submit"
            class="mt-2 inline-flex justify-center py-1 px-3 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-700 focus:outline-none">
            Attach Tickets
          </button>
        </form>
      </div>
      <div class="ml-4">
        <h4 class="text-lg font-semibold text-gray-700">Attached Tickets</h4>
        @if($project->tickets->isEmpty())
        <p class="text-gray-600">No tickets attached.</p>
        @else
        <ul class="list-none pl-2 mt-2 text-gray-600">
          @foreach ($project->tickets as $ticket)
          <li title="{!! strip_tags($ticket->notizen) !!}">
            <span class="text-blue-500 font-bold">{{ $ticket->problem_type }}</span>
            &#x2192;
            <span class="text-gray-400">{{ $ticket->subUser->vorname ?? 'N/A' }} {{ $ticket->subUser->name ?? 'N/A'
              }}</span>
            <span class="text-gray-400">{{ @$ticket->location->address}}</span>
            <form action="{{ route('projects.detach-ticket', ['project' => $project->id, 'ticket' => $ticket->id]) }}"
              method="POST" style="display: inline;"
              onsubmit="return confirm('Are you sure you want to detach this ticket?');">
              @csrf
              <button type="submit" class="text-red-500 hover:text-red-700 ml-4 text-sm">X</button>
            </form>
          </li>
          @endforeach
        </ul>
        @endif
      </div>
    </div>

    <!-- Add Device and Show Selected Devices Section -->
    <div class="grid grid-cols-2 gap-4 mt-6">
      <div>
        <div class="flex items-center">
          <h4 class="text-lg font-semibold text-gray-700 flex-grow">Add Device</h4>
          <button id="addDeviceButton" type="button"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-lg">
            +
          </button>
        </div>
        <form action="{{ route('projects.store-devices', ['project' => $project->id]) }}" method="POST" class="mt-4"
          id="addDevicesForm">
          @csrf
          <div id="deviceContainer"></div>
          <button type="submit"
            class="mt-2 bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded text-sm">
            Submit Devices
          </button>
        </form>
      </div>
<div class="ml-4">
    <h4 class="text-lg font-semibold text-gray-700">Selected Devices</h4>
    <ul class="list-none pl-2 mt-2 text-gray-600">
        @foreach ($project->devices->sortBy('name') as $device)
        <li>
            <span class="text-blue-500 font-bold">{{ $device->name }}</span>
            &#x2192;
            <span class="text-gray-400">({{ $device->pivot->quantity }})</span>
            <span class="text-gray-400">{{ $device->pivot->status ?? '' }}</span>
            @if (!empty($device->pivot->note))
            &#x2192; <span class="text-green-500">{{ $device->pivot->note }}</span>
            @else
            <span class="text-green-500">{{ $device->pivot->note }}</span>
            @endif
        </li>
        @endforeach
    </ul>
</div>

    </div>

    <!-- Todo Section -->
    <div class="mt-6">
      <h4 class="text-lg font-semibold text-gray-700">Add Todo</h4>
      <form action="" method="POST" class="mt-4">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <textarea name="description"
          class="form-textarea mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          rows="3"></textarea>
        <button type="submit"
          class="mt-2 inline-flex justify-center py-1 px-3 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-700 focus:outline-none">
          Add Todo
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
  $(document).ready(function () {
    function addDeviceField() {
      var newField =
`
<div class="grid grid-cols-12 gap-4 items-end mt-4">
    <div class="col-span-3">
        <select name="device_ids[]" class="block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
            @foreach ($devices as $device) <!-- Ensure proper syntax here -->
            <option value="{{ $device->id }}">{{ $device->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-span-2">
        <input type="number" name="quantities[]" min="1" class="block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
    </div>
    <div class="col-span-3">
        <select name="statuses[]" class="block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
            @foreach (App\Enums\DeviceStatus::getValues() as $status)
            <option value="{{ $status }}">{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-span-3">
        <input type="text" name="notes[]" placeholder="Notes" class="block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
    </div>
    <div class="col-span-1 flex justify-end items-center">
        <button type="button" class="remove-device-field bg-red-400 hover:bg-red-500 text-white font-bold py-0.5 px-1 rounded">
            X
        </button>
    </div>
</div>

`
        ;
      $('#deviceContainer').append(newField);
    }

    $('#addDeviceButton').click(function () {
      addDeviceField();
    });

    $('#deviceContainer').on('click', '.remove-device-field', function () {
      $(this).closest('.grid').remove();
    });

    $('.select2-multi').select2({
      placeholder: "Select tickets",
      allowClear: true,
      width: '100%' // Adjusts width to fit its container
    });
  });
</script>
@endsection