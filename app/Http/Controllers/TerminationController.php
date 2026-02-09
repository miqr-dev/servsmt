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
      'is_active' => 'nullable|boolean',
    ]);

    $payload = $request->except('is_active');
    $payload['is_active'] = $request->boolean('is_active', true);

    Termination::create($payload);
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
      'is_active' => 'nullable|boolean',
    ]);

    $payload = $request->except('is_active');
    $payload['is_active'] = $request->boolean('is_active', true);

    $termination->update($payload);
    $sucMsg = array(
      'message' => 'Erfolgreich bearbeitet ',
      'alert-type' => 'success'
    );

    return redirect()->route('dashboard')->with($sucMsg);
  }

  public function destroyed(Termination $termination, $id)
  {
    $termination = Termination::findOrFail($id);

    $data = $this->buildNotificationData($termination, 'deleted');

    Notification::route('mail', $this->notificationRecipients())
      ->notify(new \App\Notifications\TerminationDeletedNotification($data));

    $termination->delete();

    return 'true';
  }

  public function toggleStatus(Termination $termination)
  {
    $termination->is_active = ! $termination->is_active;
    $termination->save();

    if (! $termination->is_active) {
      $data = $this->buildNotificationData($termination, 'inactive');

      Notification::route('mail', $this->notificationRecipients())
        ->notify(new \App\Notifications\TerminationDeletedNotification($data));
    }

    $message = $termination->is_active
      ? 'Mitarbeiter wurde als aktiv markiert.'
      : 'Mitarbeiter wurde als inaktiv markiert.';

    return redirect()->back()->with([
      'message' => $message,
      'alert-type' => 'success',
    ]);
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

  /**
   * Build the notification payload for employee changes.
   */
  protected function buildNotificationData(Termination $termination, string $status): array
  {
    $titles = [
      'deleted'  => 'Mitarbeiter gelöscht',
      'inactive' => 'Mitarbeiter inaktiv',
    ];

    $messages = [
      'deleted'  => $termination->name . ' aus ' . $termination->location . ' wurde gelöscht.',
      'inactive' => $termination->name . ' aus ' . $termination->location . ' wurde als inaktiv markiert.',
    ];

    $title = $titles[$status] ?? 'Mitarbeiter aktualisiert';

    return [
      'title'        => $title,
      'id'           => $termination->id,
      'name'         => $termination->name,
      'location'     => $termination->location,
      'occupation'   => $termination->occupation,
      'status'       => $status,
      'mail_subject' => $title,
      'mail_line'    => $messages[$status] ?? ($termination->name . ' aus ' . $termination->location . ' wurde aktualisiert.'),
    ];
  }

  /**
   * Retrieve a sanitized list of notification recipients.
   */
  protected function notificationRecipients(): array
  {
    return collect([
      'ara.matoyan@miqr.de',
      'Katharina.Rempel@miqr.de',
      'Matthias.Kirchner@miqr.de',
      'Denise.Naue@miqr.de',
      'Denise.Naue@miqr.de',
      'Leah.Wittek@miqr.de',
      'Jens.Ebermann@miqr.de',
    ])
      ->map(function ($e) {
        return trim($e);
      })
      ->filter(function ($e) {
        return filter_var($e, FILTER_VALIDATE_EMAIL);
      })
      ->unique()
      ->values()
      ->all();
  }
}
