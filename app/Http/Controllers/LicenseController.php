<?php

namespace App\Http\Controllers;

use App\User;
use App\License;
use App\Ticket;
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
  // The former /dashboard page (licenses, terminations, forwardings) moved
  // to the unified Dashboard (DashboardController / pages/Home.vue).
  // Own page for all licences (sidebar "Lizenzen", Super_Admin only via
  // route_access 'licenses*'). The Dashboard box shows only the expiring ones.
  public function index()
  {
    return \Inertia\Inertia::render('Licenses/Index', [
      'licenses' => License::orderByRaw('CASE WHEN valid IS NULL THEN 1 ELSE 0 END')
        ->orderBy('valid', 'ASC')
        ->get(),
    ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  // Neu / Bearbeiten are dialogs (components/dashboard/LicensesBox.vue).
  public function create()
  {
    return redirect()->route('licenses.index');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    License::create($this->validated($request));

    return back()->with('success', 'Lizenz hinzugefügt.');
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
    return redirect()->route('licenses.index');
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
    $license->update($this->validated($request));

    return back()->with('success', 'Lizenz gespeichert.');
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

    return back()->with('success', 'Lizenz gelöscht.');
  }

  /** Shared rules for the Neu / Bearbeiten dialog. */
  private function validated(Request $request): array
  {
    return $request->validate([
      'name' => 'required|string|max:255',
      'where' => 'nullable|string|max:255',
      'version' => 'nullable|string|max:255',
      'valid' => 'nullable|date',
      'comment' => 'nullable|string|max:2000',
    ]);
  }
}
