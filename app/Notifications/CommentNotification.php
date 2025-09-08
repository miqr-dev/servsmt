<?php

namespace App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CommentNotification extends Notification
{
  use Queueable;
  protected $comment;

  public function __construct($comment)
  {
    $this->myComment = $comment;
  }
  public function via($notifiable)
  {
    return ['mail', 'database'];
  }
  public function toMail($notifiable)
  {
    $id = $this->myComment['id'];
    $type = $this->myComment['type'] ?? 'ticket'; // fallback

    switch ($type) {
      case 'Handwerk':
        $routeBase = 'handwerk';
        break;
      case 'Korso':
        $routeBase = 'korso';
        break;
      default:
        $routeBase = 'ticket';
        break;
    }


    $url = url("http://servsmt.miqr.local/{$routeBase}/{$id}");
    $replier = $this->myComment['commenter_id'];
    $comment_body = $this->myComment['comment'];

    return (new MailMessage)
      ->subject('Kommentarantwort')
      ->line($replier . ' hat auf deinen Kommentar geantwortet.')
      ->line($comment_body)
      ->action('anzeigen', $url);
  }

  public function toDatabase($notifiable)
  {
    return [
      'title' => $this->myComment['title'],
      'id' => $this->myComment['id'], //commentable_id
      'comment' => $this->myComment['comment'],
      'commenter' => $this->myComment['commenter_id'],
      'type' => strtolower($this->myComment['type'] ?? 'ticket'),
    ];
  }
  
  public function toArray($notifiable)
  {
    return [
      'title' => $this->myComment['title'],
      'id' => $this->myComment['id'],
      'comment' => $this->myComment['comment'],
      'commenter' => $this->myComment['commenter_id'],
      'type' => strtolower($this->myComment['type'] ?? 'ticket'),
    ];
  }
}
