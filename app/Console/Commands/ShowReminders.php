<?php

namespace App\Console\Commands;

use Log;
use App\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Notifications\ReminderNotification;


class ShowReminders extends Command
{
  protected $signature = 'reminders:show';

  protected $description = 'Show upcoming reminders';

  public function handle()
  {
    $now = Carbon::now();

    // Retrieve reminders that are scheduled for today
    $reminders = Reminder::whereDate('reminder_date', '=', $now->toDateString())
      ->where('is_reminded', false) // or ->where('is_reminded', 0)
      ->get();

    // Debug: Output the count of reminders found
    Log::info("Found " . $reminders->count() . " reminders for today.");

    // Loop through each reminder
    foreach ($reminders as $reminder) {
      $user = $reminder->user;
      try {
        $user->notify(new ReminderNotification($reminder));
      } catch (\Exception $e) {
        // Debug: Log any exceptions
        \Log::error("Failed to send reminder notification to User: {$user->id}. Error: {$e->getMessage()}");
      }
    }

    // Additional debug: Check if there were no reminders and log it
    if ($reminders->isEmpty()) {
      \Log::info("No reminders to send for today.");
    }
  }
}
