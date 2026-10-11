<?php

namespace App\Http\Controllers;

use App\Place;
use App\Ticket;
use App\Standortbesuch;
use Illuminate\Http\Request;

class StandortbesuchController extends Controller
{


  public function standortbesuch_berlin(Request $request)
  {
    $standortbesuch_berlin = Standortbesuch::find('1');
    $standortbesuch_berlin->berlin = $request->datum_berlin;
    $standortbesuch_berlin->save();
  }
  public function standortbesuch_berlinii(Request $request)
  {
    $standortbesuch_berlinii = Standortbesuch::find('1');
    $standortbesuch_berlinii->berlinii = $request->datum_berlinii;
    $standortbesuch_berlinii->save();
  }
  public function standortbesuch_chemnitz(Request $request)
  {
    $standortbesuch_chemnitz = Standortbesuch::find('1');
    $standortbesuch_chemnitz->chemnitz = $request->datum_chemnitz;
    $standortbesuch_chemnitz->save();
  }
  public function standortbesuch_dresden(Request $request)
  {
    $standortbesuch_dresden = Standortbesuch::find('1');
    $standortbesuch_dresden->dresden = $request->datum_dresden;
    $standortbesuch_dresden->save();
  }
  public function standortbesuch_leipzig(Request $request)
  {
    $standortbesuch_leipzig = Standortbesuch::find('1');
    $standortbesuch_leipzig->leipzig = $request->datum_leipzig;
    $standortbesuch_leipzig->save();
  }
  public function standortbesuch_suhl(Request $request)
  {
    $standortbesuch_suhl = Standortbesuch::find('1');
    $standortbesuch_suhl->suhl = $request->datum_suhl;
    $standortbesuch_suhl->save();
  }

  public function index()
  {
    $datum = Standortbesuch::find(1);
    return view('settings.publish.standortbesuch', compact('datum'));
  }

// getCityTicketDetail() removed 2026-10-09: only the old Blade ticket table used it (city tickets + "Hinweise").


  public function create()
  {
    //
  }

  public function store(Request $request)
  {
    //
  }

  public function show(Standortbesuch $standortbesuch)
  {
    //
  }

  public function edit(Standortbesuch $standortbesuch)
  {
    //
  }

  public function update(Request $request, Standortbesuch $standortbesuch)
  {
    //
  }

  public function destroy(Standortbesuch $standortbesuch)
  {
    //
  }
}
