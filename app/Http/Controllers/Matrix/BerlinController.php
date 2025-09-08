<?php

namespace App\Http\Controllers\Matrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Berlin;

class BerlinController extends Controller
{

  function __construct()
  {
  // $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
  // $this->middleware('permission:role-create', ['only' => ['create','store']]);
  // $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
  // $this->middleware('permission:role-delete', ['only' => ['destroy']]);

  $this->middleware('role:Super_Admin')->only('index');
  }
    public function index()
    {
      $matrix = Berlin::all();
      $section = Berlin::pluck('section')->unique();
      $area = Berlin::pluck('area')->unique();
      return view('matrix.berlin.trachenberg93',compact('matrix','section','area'));
    }
}
