<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HandwerkNotification extends Notification
{
  use Queueable;
  protected $handwerk;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct($notifications)
  {
    $this->myData = $notifications;
  }

  public function via($notifiable)
  {
    return ['mail', 'database'];
  }

  // public function toMail($notifiable)
  // {
  //   $url = url('http://servsmt.miqr.local/handwerk/' . $this->myData['ticket_id']);
  //   $ersteller = $this->myData['submitter'];
  //   $problem_type = $this->myData['problem_type'];
  //   $response =  (new MailMessage);
  //   if ($this->myData['title'] === 'Neues Ticket') {
  //     $response->subject('Ein neues Ticket von ' . $ersteller)
  //       ->line('Ein neues Ticket wurde von ' . $ersteller . ' eingereicht')
  //       ->line($problem_type)
  //       ->action('Ticket anzeigen', $url)
  //       ->line(
  //         '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>'
  //       );
  //   }
  //   if ($this->myData['title'] === 'Zugewiesen an') {
  //     $response->subject('Handwerkerticket benachrichtigung')
  //       ->line('Sie wurden für die folgende Aufgabe zugewiesen')
  //       ->line($problem_type)
  //       ->action('Ticket anzeigen', $url)
  //       ->line(
  //         '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>'
  //       );
  //   }
  //   if ($this->myData['title'] === 'Erledigt') {
  //     $response->subject('Handwerkerticket benachrichtigung')
  //       ->line('Ihr eingereichtes Ticket wird als erledigt markiert ')
  //       ->line($problem_type)
  //       ->action('Ticket anzeigen', $url)
  //       ->line(
  //         '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen" und Ticket wiederherstellen.</p>'
  //       );
  //   }
  //   if ($this->myData['title'] === 'Wiederhergestellt') {
  //     $date = $this->myData['date'];
  //     $response->subject('Handwerkerticket benachrichtigung')
  //       ->line('Das Ticket, das von ' . $ersteller . ' am ' . $date . ' eingereicht wurde, wird wiederhergestellt.')
  //       ->line($problem_type)
  //       ->action('Ticket anzeigen', $url)
  //       ->line(
  //         '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>'
  //       );
  //   }
  //   return $response;
  // }

  public function toMail($notifiable)
  {
    $url = url('http://servsmt.miqr.local/handwerk/'.$this->myData['ticket_id']);
    $ersteller = $this->myData['submitter'];
    $problem_type = $this->myData['problem_type'];
    $response = (new MailMessage);

    if ($this->myData['title'] === 'Neues Handwerk Ticket') {
      $response->subject('Ein neues Ticket von ' . $ersteller)
        ->line('Ein neues Ticket wurde von ' . $ersteller . ' eingereicht')
        ->line($problem_type)
        ->action('Ticket anzeigen', $url)
        ->line('<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>');
    }

    if ($this->myData['title'] === 'Zugewiesen an') {
      $response->subject('Handwerkerticket benachrichtigung')
        ->line('Sie wurden für die folgende Aufgabe zugewiesen')
        ->line($problem_type)
        ->action('Ticket anzeigen', $url)
        ->line('<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>');

      if (isset($this->myData['pdf_path'])) {
        $response->attach($this->myData['pdf_path'], [
          'as' => 'ticket_details.pdf',
          'mime' => 'application/pdf',
        ]);
      }
    }

    if ($this->myData['title'] === 'Erledigt') {
      $response->subject('Handwerkerticket benachrichtigung')
        ->line('Ihr eingereichtes Ticket wird als erledigt markiert ')
        ->line($problem_type)
        ->action('Ticket anzeigen', $url)
        ->line('<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen" und Ticket wiederherstellen.</p>');
    }

    if ($this->myData['title'] === 'Wiederhergestellt') {
      $date = $this->myData['date'];
      $response->subject('Handwerkerticket benachrichtigung')
        ->line('Das Ticket, das von ' . $ersteller . ' am ' . $date . ' eingereicht wurde, wird wiederhergestellt.')
        ->line($problem_type)
        ->action('Ticket anzeigen', $url)
        ->line('<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>');
    }

    return $response;
  }

  public function toDatabase($notifiable)
  {
    return [
      'title' => $this->myData['title'],
      'id' => $this->myData['ticket_id'],
      'ersteller' => $this->myData['submitter'],
      'problem_type' => $this->myData['problem_type'],
    ];
  }
  // public function toArray($notifiable)
  // {
  //   return [
  //     //
  //   ];
  // }
}
