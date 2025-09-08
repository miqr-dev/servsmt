<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TicketNotification extends Notification
{
    use Queueable;
    protected $ticket;

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
        return ['mail','database'];
    }
    public function toMail($notifiable)
    {
      $url = url('http://servsmt.miqr.local/ticket/'.$this->myData['ticket_id']);
      $ersteller = $this->myData['submitter'];
      $problem_type = $this->myData['problem_type'];
      $response =  (new MailMessage);
                    
        if($this->myData['title'] === 'Neues Ticket' || $this->myData['title'] === 'Neuer Teilnehmer' ){
          $response->subject('Ticketbenachrichtigung')
                  ->line('Ein neues Ticket wurde von '. $ersteller .' eingereicht')
                  ->line($problem_type)
                  ->action('Ticket anzeigen', $url)
                  ->line(
                  '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>'
                  );
        }
        if($this->myData['title'] === 'Zugewiesen an'){
          $response->subject('Ticketbenachrichtigung')
                  ->line('Sie wurden für die folgende Aufgabe zugewiesen')
                  ->line($problem_type)
                  ->action('Ticket anzeigen', $url)
                  ->line(
                  '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>'
                  );
        }
        if($this->myData['title'] === 'Wiederhergestellt'){
          $date = $this->myData['date'];
          $response->subject('Ticketbenachrichtigung')
                  ->line('Das Ticket, das von ' . $ersteller . ' am ' . $date . ' eingereicht wurde, wird wiederhergestellt.')
                  ->line($problem_type)
                  ->action('Ticket anzeigen', $url)
                  ->line(
                  '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen"</p>'
                  );
        }
        if($this->myData['title'] === 'Erledigt'){
          $response->subject('Ticketbenachrichtigung')
                  ->line('Ihr eingereichtes Ticket wird als erledigt markiert ')
                  ->line($problem_type)
                  ->action('Ticket anzeigen', $url)
                  ->line(
                  '<br><p style="color: red;">Dies ist eine automatisch generierte E-Mail, bitte nicht antworten. Für weitere Anfragen klicken Sie bitte auf "Ticket anzeigen" und Ticket wiederherstellen.</p>'
                  );
        }
        if($this->myData['title'] === 'Neuer Mitarbeiter'){
          $response->subject('Neuer Mitarbeiter')
                  ->line('<h1>Neuer Mitarbeiter/in wurde angelegt</h1>')
                  ->line('<p>am <strong>'. $this->myData['employee_required_at'] . ' </strong> beginnt in <strong> ' . $this->myData['employee_city'] . ' ' . $this->myData['employee_address'] . '</strong></p>')
                  ->line(
                  '<p>Vorname &#8594; <strong>' . $this->myData['employee_firstname'] . '</strong> <br/>' . 
                  'Nachname &#8594; <strong>' . $this->myData['employee_lastname'] . '</strong> <br/>' .
                  'Position &#8594;<strong> ' . $this->myData['position_employee'] . '</strong> <br/>' .
                  'Abteilung &#8594;<strong> ' . $this->myData['abteilung_employee'] . '</strong> <br/>' .
                  'Telefon &#8594;<strong> ' . $this->myData['telephone_employee'] . '</p>'
                  )
                  ->line('<h2><u>Windows</u></h2>' . 
                  '<p>Benutzername &#8594; <strong> ' . $this->myData['employee_username'] . '</strong> <br/>' .
                  'Passwort &#8594; <strong>Miqr.2025# </strong> <br/>' . 
                  'Email &#8594; <strong>' . $this->myData['employee_email'] . '</strong> </p>'
                  );
                  if($this->myData['isplus'] == true) {
                  $response->line('<h2><u> IS+ </u></h2> 
                  <p><strong>Zur Anmeldung an IS+, bitte den Haken „Windows Authentifizierung“ setzen.</strong></p>');
                  
                  };
                  $response->line('<p>' . $this->myData['message'] . '</p>'); 
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // public function toArray($notifiable)
    // {
    //     return [
    //         //
    //     ];
    // }

}
