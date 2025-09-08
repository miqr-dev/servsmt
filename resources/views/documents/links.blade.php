@extends('layouts.admin_layout.admin_layout')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold text-center mb-6">Generierte PDFs</h2>
    <div class="flex justify-center space-x-4 flex-wrap">
        @foreach($documentLinks as $city => $links)
            <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 w-72 shadow-md mb-6">
                <h3 class="text-lg font-bold text-center text-gray-700 mb-4">{{ $city }}</h3>
                <div class="space-y-2">
                    @foreach($links as $link)
                        <a href="{{ $link }}" 
                           target="_blank" 
                           class="block text-center bg-blue-500 hover:bg-blue-600 focus:bg-green-500 visited:bg-green-500 text-white py-2 rounded transition duration-300 no-underline focus:no-underline visited:no-underline">
                            {{ basename($link) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('styles')
<style>
    .no-underline {
        text-decoration: none;
    }
</style>
@endsection
