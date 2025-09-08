<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KorsoNotification extends Notification
{
  use Queueable;

  public function __construct($notifications)
  {
    $this->myData = $notifications;
  }

  public function via($notifiable)
  {
    return ['mail', 'database'];
  }

  public function toMail($notifiable)
  {
    $korsoUrl = url('http://servsmt.miqr.local/korso/' . $this->myData['korso_id']);
    $ersteller = $this->myData['submitter'];
    $problem_type = $this->myData['problem_type'];

    // Prepare variables for the email
    $subject = '';
    $introLines = [];
    $actionText = 'Ticket anzeigen';
    $outroLines = ['<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail. Bitte nicht antworten.</p>'];

    // Customize message content based on the notification title
    if ($this->myData['title'] === 'Neues Korso Ticket') {
      $subject = '🔴 Neues Hoch-Prioritäts-Korso-Ticket';
      $introLines[] = 'Ein neues Ticket mit Priorität *Hoch* wurde eingereicht.';
      $introLines[] = "Erstellt von: **$ersteller**";
      $introLines[] = "Typ: **$problem_type**";
    } elseif ($this->myData['title'] === 'Zugewiesen an') {
      $subject = 'Korso-Benachrichtigung';
      $introLines[] = 'Sie wurden einem neuen Korso-Ticket zugewiesen.';
      $introLines[] = $this->myData['problem_type'];
    } elseif ($this->myData['title'] === 'Erledigt') {
      $subject = 'Ihr Ticket ist erledigt';
      $introLines[] = 'Ihr Ticket wurde erfolgreich bearbeitet und als erledigt markiert.';
    } elseif ($this->myData['title'] === 'Wiederhergestellt') {
      $subject = 'Korso Ticket Wiederhergestellt';
      $introLines[] = 'Ein Korso-Ticket wurde wiederhergestellt.';
    }

    // Return a Markdown mail message using your custom template in vendor/notifications/korso.blade.php.
    return (new MailMessage)
      ->subject($subject)
      ->markdown('vendor.notifications.korso', [
        'greeting'      => $subject,        // You can set a greeting based on your subject
        'level'         => 'primary',       // Adjust the level if needed (affects button color)
        'introLines'    => $introLines,
        'actionText'    => $actionText,
        'actionUrl'     => $korsoUrl,
        'outroLines'    => $outroLines,
        // 'salutation'    => 'Mit freundlichen Grüßen,<br>Ihre Korso-Team',
      ]);
  }

  public function toDatabase($notifiable)
  {
    return [
      'title' => $this->myData['title'],
      'korso_id' => $this->myData['korso_id'],
      'submitter' => $this->myData['submitter'],
      'problem_type' => $this->myData['problem_type'],
    ];
  }

  public function toArray($notifiable)
  {
    return [
      //
    ];
  }
}
