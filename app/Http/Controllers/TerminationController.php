<?php

namespace App\Http\Controllers;

use App\User;
use App\Termination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\TerminationsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TerminationDeletedNotification;


class TerminationController extends Controller
{




  public function index()
  {
    //
  }

  public function create()
  {
    return view('termination.create');
  }

  public function createUpload()
  {
    return view('termination.upload_termination');
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required',
      'location' => 'required',
      'exit' => 'required',
    ]);

    Termination::create($request->all());
    $sucMsg = array(
      'message' => 'Erfolgreich hinzugefügt ',
      'alert-type' => 'success'
    );

    return redirect()->route('dashboard')->with($sucMsg);
  }


  public function storeUpload(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'file' => 'required|max:20000|mimes:xlsx,xls',
    ]);
    if ($validator->fails()) {
      return back()
        ->withErrors($validator);
    }
    DB::table('terminations')->truncate();
    Excel::import(new TerminationsImport, $request->file('file'));
    return redirect()->route('dashboard');
  }

  public function show(Termination $termination)
  {
    //
  }

  public function edit(Termination $termination)
  {
    return view('termination.edit', compact('termination'));
  }

  public function update(Request $request, Termination $termination)
  {
    $request->validate([
      'name' => 'required',
      'location' => 'required',
      'exit' => 'required',
    ]);

    $termination->update($request->all());
    $sucMsg = array(
      'message' => 'Erfolgreich bearbeitet ',
      'alert-type' => 'success'
    );

    return redirect()->route('dashboard')->with($sucMsg);
  }

  public function destroyed(Termination $termination, $id)
  {
    $termination = Termination::findOrFail($id);

    $data = [
      'title'      => 'Mitarbeiter gelöscht',
      'id'         => $termination->id,
      'name'       => $termination->name,
      'location'   => $termination->location,
      'occupation' => $termination->occupation,
    ];

    // Build recipients with a collection, validate & dedupe
    $recipients = collect([
      'ara.matoyan@miqr.de',
      'Katharina.Rempel@miqr.de',
      'Matthias.Kirchner@miqr.de',
    ])
      ->map(function ($e) {
        return trim($e);
      })
      ->filter(function ($e) {
        return filter_var($e, FILTER_VALIDATE_EMAIL);
      })
      ->unique()
      ->values()
      ->all(); // => array of unique, valid emails

    Notification::route('mail', $recipients)
      ->notify(new \App\Notifications\TerminationDeletedNotification($data));

    $termination->delete();

    return 'true';
  }
  public function history()
  {
    $terminations = Termination::onlyTrashed()->get();
    return view('termination.history', compact('terminations'));
  }

  public function restore($id)
  {
    Termination::withTrashed()->find($id)->restore();
    return back();
  }

  public function toggleStatus($id)
  {
    $termination = Termination::findOrFail($id);
    $termination->is_inactive = !$termination->is_inactive;
    $termination->save();

    return response()->json(['success' => true]);
  }
}
