<?php

namespace App\Listeners;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class UpdateReminderStatus
{
public function handle(NotificationSent $event)
{
    Log::info('UpdateReminderStatus listener triggered.');

    if ($event->notification instanceof ReminderNotification) {
        $reminder = $event->notifiable->reminders()->find($event->notification->reminder->id);

        if ($reminder) {
            $reminder->is_reminded = 1;
            $reminder->save();
            Log::info('Reminder updated with ID: ' . $reminder->id);
        } else {
            Log::info('No matching reminder found.');
        }
    }
}
}
