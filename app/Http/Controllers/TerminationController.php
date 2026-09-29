<?php

namespace App\Http\Controllers;

use App\User;
use App\Termination;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Imports\TerminationsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TerminationDeletedNotification;


class TerminationController extends Controller
{




  // Own page for the Kündigungen table (sidebar "Kündigungen", HR).
  // Same component as the Dashboard box, just without the height limit.
  public function index()
  {
    return Inertia::render('Terminations/Index', [
      'terminations' => Termination::orderBy('exit', 'ASC')->get(),
    ]);
  }

  // Neu / Bearbeiten are dialogs on the Dashboard now (TerminationsBox.vue);
  // the old Blade pages are no longer used.
  public function create()
  {
    return redirect()->route('home');
  }

  public function createUpload()
  {
    return view('termination.upload_termination');
  }

  public function store(Request $request)
  {
    Termination::create($this->validated($request));

    return back()->with('success', 'Kündigung hinzugefügt.');
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
    return redirect()->route('home');
  }

  public function update(Request $request, Termination $termination)
  {
    $termination->update($this->validated($request));

    return back()->with('success', 'Kündigung gespeichert.');
  }

  /** Shared rules for Neu / Bearbeiten (dialog on the Dashboard). */
  private function validated(Request $request): array
  {
    $data = $request->validate([
      'name' => 'required|string|max:255',
      'location' => 'required|string|max:255',
      'occupation' => 'nullable|string|max:255',
      'exit' => 'required|date',
      'is_active' => 'nullable|boolean',
    ]);
    $data['is_active'] = $request->boolean('is_active', true);

    return $data;
  }

  /**
   * "Entfernen": the Kündigung is no longer needed (withdrawn, contract
   * renewed, ...). Leaves the list like a delete, but keeps reason / who /
   * when for the Verlauf, and says so in the mail.
   */
  public function remove(Request $request, $id)
  {
    $termination = Termination::findOrFail($id);
    $data = $request->validate([
      'reason' => ['required', Rule::in(array_keys(Termination::REMOVAL_REASONS))],
      'note' => 'nullable|string|max:1000|required_if:reason,other',
    ], [
      'note.required_if' => 'Bitte eine Begründung angeben.',
    ]);

    $termination->removal_reason = $data['reason'];
    $termination->removal_note = $data['note'] ?? null;
    $termination->removed_by = auth()->id();
    $termination->removed_at = now();
    $termination->save();

    $mail = $this->buildNotificationData($termination, 'removed');
    \App\Support\Notify::one(Notification::route('mail', $this->notificationRecipients()), new \App\Notifications\TerminationDeletedNotification($mail));

    $termination->delete();

    return back()->with('success', $termination->name.' wurde aus der Liste entfernt.');
  }

  public function destroyed(Termination $termination, $id)
  {
    $termination = Termination::findOrFail($id);

    $data = $this->buildNotificationData($termination, 'deleted');

    \App\Support\Notify::one(Notification::route('mail', $this->notificationRecipients()), new \App\Notifications\TerminationDeletedNotification($data));

    $termination->delete();

    // Dashboard (Inertia) goes back; the old jQuery AJAX still gets 'true'.
    if (request()->header('X-Inertia')) {
      return back();
    }

    return 'true';
  }

  public function toggleStatus(Termination $termination)
  {
    $termination->is_active = ! $termination->is_active;
    $termination->save();

    if (! $termination->is_active) {
      $data = $this->buildNotificationData($termination, 'inactive');

      \App\Support\Notify::one(Notification::route('mail', $this->notificationRecipients()), new \App\Notifications\TerminationDeletedNotification($data));
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
    $rows = Termination::onlyTrashed()
      ->with('removedByUser')
      ->orderByDesc('deleted_at')
      ->get()
      ->map(function (Termination $t) {
        $by = $t->removedByUser;

        return [
          'id' => $t->id,
          'name' => $t->name,
          'location' => $t->location,
          'occupation' => $t->occupation,
          'exit' => optional($t->exit)->toDateString(),
          'deleted_at' => optional($t->deleted_at)->toIso8601String(),
          // "removed" = Entfernt (with reason), otherwise a plain Löschen.
          'kind' => $t->removal_reason ? 'removed' : 'deleted',
          'reason' => $t->removal_reason ? (Termination::REMOVAL_REASONS[$t->removal_reason] ?? $t->removal_reason) : null,
          'note' => $t->removal_note,
          'removed_by' => $by ? trim($by->vorname.' '.$by->name) : null,
        ];
      })
      ->values();

    return Inertia::render('Terminations/History', ['terminations' => $rows]);
  }

  public function restore($id)
  {
    $termination = Termination::withTrashed()->findOrFail($id);
    $termination->restore();
    // Back on the list - the removal info no longer applies.
    $termination->forceFill([
      'removal_reason' => null,
      'removal_note' => null,
      'removed_by' => null,
      'removed_at' => null,
    ])->save();

    return back()->with('success', $termination->name.' wurde wiederhergestellt.');
  }

  /**
   * Build the notification payload for employee changes.
   */
  protected function buildNotificationData(Termination $termination, string $status): array
  {
    $titles = [
      'deleted'  => 'Mitarbeiter gelöscht',
      'inactive' => 'Mitarbeiter inaktiv',
      'removed'  => 'Kündigung entfernt',
    ];

    $reason = Termination::REMOVAL_REASONS[$termination->removal_reason] ?? null;
    $removedText = $termination->name . ' aus ' . $termination->location . ' wurde aus der Kündigungsliste entfernt'
      . ($reason ? ' (' . $reason . ($termination->removal_note ? ': ' . $termination->removal_note : '') . ')' : '') . '.';

    $messages = [
      'deleted'  => $termination->name . ' aus ' . $termination->location . ' wurde gelöscht.',
      'inactive' => $termination->name . ' aus ' . $termination->location . ' wurde als inaktiv markiert.',
      'removed'  => $removedText,
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
