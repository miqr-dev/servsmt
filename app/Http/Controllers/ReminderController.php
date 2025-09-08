<?php

namespace App\Http\Controllers;

use App\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
  public function markAsReadAjax($id)
  {
    $notification = auth()->user()->unreadNotifications->where('id', $id)->first();

    if ($notification) {
      $notification->markAsRead();
      return response()->json(['status' => 'success']);
    }

    return response()->json(['status' => 'error'], 404);
  }

  public function toggleRemind(Request $request, Reminder $reminder)
  {
    $reminder->update([
      'is_reminded' => !$reminder->is_reminded
    ]);

    return response()->json([
      'success' => true,
      'isReminded' => $reminder->is_reminded,
    ]);
  }

  public function index()
  {
    $now = Carbon::now();
    // Fetch reminders where 'reminder_date' is today and 'is_reminded' is false
    $reminders = Reminder::all();

    return view('settings.reminders.index', compact('reminders'));
  }

  public function store(Request $request)
  {
    Reminder::create($request->all());
    return redirect()->route('reminders.index')->with('success', 'Reminder created successfully.');
  }

  public function show(Reminder $reminder)
  {
    return view('reminders.show', compact('reminder'));
  }

  public function edit(Reminder $reminder)
  {
    return view('reminders.edit', compact('reminder'));
  }

  public function update(Request $request, Reminder $reminder)
  {
    $reminder->update($request->all());
    return redirect()->route('reminders.index')->with('success', 'Reminder updated successfully');
  }

  public function destroy(Reminder $reminder)
  {
    $reminder->delete();
    return redirect()->route('reminders.index')->with('success', 'Reminder deleted successfully');
  }
}
