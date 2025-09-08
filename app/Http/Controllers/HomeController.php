<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
      // $hour = date('H');
      // $dayTerm = ($hour > 17) ? "Guten Abend" : (($hour > 12) ? "Guten Tag" : "Guten Morgen");
      // $user = Auth()->user();
      // return view('admin/admin_dashboard',compact('user','dayTerm'));

      return view('wilkommen');
    }
}
