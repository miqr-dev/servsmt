@extends('layouts.admin_layout.admin_layout')

@section('content')
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
    <div class="flex justify-between items-center pb-4">
        <h1 class="text-lg pb-4">Gerätestatus</h1>
        <a href="{{ route('device-statuses.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Status hinzufügen</a>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-2 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-5 py-2 border-b-2 border-gray-200 bg-gray-100"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statuses as $status)
                <tr>
                    <!-- Reduced padding from py-5 to py-2 -->
                    <td class="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                        {{ $status->name }}
                    </td>
                    <td class="px-5 py-2 border-b border-gray-200 bg-white text-sm text-right">
                        <a href="{{ route('device-statuses.edit', $status->id) }}" class="text-indigo-600 hover:text-indigo-900">Bearbeiten</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('script')
@endsection
