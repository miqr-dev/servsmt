<?php

namespace App\Notifications;

use App\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReminderNotification extends Notification
{
  use Queueable;
  public $reminder;

  public function __construct(Reminder $reminder)
  {
    $this->reminder = $reminder;
  }

  public function via($notifiable)
  {
    return ['database', 'mail'];
  }

  public function toDatabase($notifiable)
  {
    // Load the ticket details
    $ticket = $this->reminder->ticket;

    return [
      'ticket_id' => $this->reminder->ticket_id,
      'ticket_title' => $ticket ? $ticket->problem_type : null, // Replace 'title' with the actual field name in your Ticket model
      'ticket_description' => $ticket ? $ticket->description : null, // Same as above
      'user_id' => $this->reminder->user_id,
      'date' => $this->reminder->reminder_date,
    ];
  }

  public function toArray($notifiable)
  {
    return [
      //
    ];
  }

  public function toMail($notifiable)
  {
    $ticket = $this->reminder->ticket;

    return (new MailMessage)
      ->greeting('Hello!')
      ->subject('Reminder for Your Ticket')
      ->line('You have a reminder for the ticket: ' . $ticket->problem_type)
      ->action('View Ticket', url('/ticket/' . $this->reminder->ticket_id))
      ->line('Thank you for using our application!');
  }
}
