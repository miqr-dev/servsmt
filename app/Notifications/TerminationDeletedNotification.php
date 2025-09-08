<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TerminationDeletedNotification extends Notification
{
  use Queueable;

  /**
   * Details for the deleted employee.
   *
   * @var array
   */
  protected $myData;

  /**
   * Create a new notification instance.
   *
   * @param  array  $data
   * @return void
   */
  public function __construct($data)
  {
    $this->myData = $data;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable)
  {
    return ['mail', 'database'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail($notifiable)
  {
    return (new MailMessage)
      ->subject('Mitarbeiter gelöscht')
      ->line($this->myData['name'] . ' aus ' . $this->myData['location'] . 'wurde gelöscht.');
  }

  /**
   * Get the array representation of the notification for database storage.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toDatabase($notifiable)
  {
    return [
      'title' => $this->myData['title'],
      'id' => $this->myData['id'],
      'name' => $this->myData['name'],
      'location' => $this->myData['location'],
      'occupation' => $this->myData['occupation'],
    ];
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable)
  {
    return $this->toDatabase($notifiable);
  }
}
