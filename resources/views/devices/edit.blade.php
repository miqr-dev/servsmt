@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <form method="POST" action="{{ route('devices.update', $device) }}">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Gerätenamen
                </label>
                <input type="text" name="name" id="name" value="{{ $device->name }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Aktualisieren
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
