<?php

namespace App\Http\Controllers;

use App\CityNote;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
    $cityNotes = CityNote::find(1); 
    return view('tasks.index',compact('cityNotes'));
    }
}
