<?php

namespace App\Http\Controllers;

use App\User;
use App\License;
use App\Termination;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $user = Auth()->user();
    if ($user->hasAnyRole(['Super_Admin', 'HR'])) {
      $licenses = License::orderByRaw('CASE WHEN valid IS NULL THEN 0 ELSE 1 END DESC')->orderBy('valid', 'ASC')->get();
      $month = Carbon::now()->addDays(30);
      $week = Carbon::now()->addDays(7);
      $terminations = Termination::orderBy('exit', 'ASC')->get();
      return view('wilkommen', compact('user', 'licenses', 'month', 'week', 'terminations'));
    } else {
      return redirect('/');
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('licenses.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required',
    ]);

    License::create($request->all());

    return redirect()->route('dashboard')->with('success', 'Product created successfully.');
  }


  /**
   * Display the specified resource.
   *
   * @param  \App\License  $license
   * @return \Illuminate\Http\Response
   */
  public function show(License $license)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\License  $license
   * @return \Illuminate\Http\Response
   */
  public function edit(License $license)
  {
    return view('licenses.edit', compact('license'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\License  $license
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, License $license)
  {
    $request->validate([
      'name' => 'required',
    ]);

    $license->update($request->all());

    return redirect()->route('dashboard')->with('success', 'License updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\License  $license
   * @return \Illuminate\Http\Response
   */
  public function destroy(License $license)
  {
    $license->delete();

    return redirect()->route('dashboard')
      ->with('success', 'Product deleted successfully');
  }
}
