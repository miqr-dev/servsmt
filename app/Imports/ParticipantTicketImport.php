<?php

namespace App\Imports;

use App\Ticket;
use App\ParticipantTicketTable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class ParticipantTicketImport implements ToModel, WithValidation, WithHeadingRow, WithMultipleSheets
{
  public $id;
  public $location;

  public function __construct($id, $location)
  {
    $this->id = $id;
    $this->location = $location;
  }

  public function sheets(): array
  {
    return [
      0 => $this, // Only the first sheet will be read
    ];
  }
  // public function generateRandomString($length = 25)
  // {
  //   $digits    = array_flip(range('0', '9'));
  //   $lowercase = array_flip(range('a', 'z'));
  //   $uppercase = array_flip(range('A', 'Z'));
  //   //$special   = array_flip(str_split('!@#$%^&*()_+=-}{[}]\|;:<>?/'));
  //   $combined  = array_merge($digits, $lowercase, $uppercase);

  //   $password  = str_shuffle(array_rand($digits) .
  //     array_rand($lowercase) .
  //     array_rand($uppercase) .
  //     //array_rand($special) . 
  //     implode(array_rand($combined, rand($length, $length))));

  //   return $password;
  // }


public function generateRandomString($length = 25)
{
    // Generate arrays excluding the undesired characters
    $digits    = array_flip(array_diff(range('2', '9'), ['0', '1']));         // Excludes '0' and '1
    $lowercase = array_flip(array_diff(range('a', 'z'), ['o', 'l']));       // Excludes 'o' and 'l'
    $uppercase = array_flip(array_diff(range('A', 'Z'), ['I', 'O']));       // Excludes 'I' and 'O'
    
    // Merge all allowed characters into one combined array
    $combined  = array_merge($digits, $lowercase, $uppercase);

    // Ensure at least one character from each set is included, then add $length random characters from the combined array
    $password  = str_shuffle(
        array_rand($digits) .
        array_rand($lowercase) .
        array_rand($uppercase) .
        implode(array_rand($combined, $length))
    );

    return $password;
}

  public function rules(): array
  {
    return [
      'vorname' =>  ['required', 'string'],
      'nachname' =>  ['required', 'string'],
    ];
  }

  public function model(array $row)
  {
    return new ParticipantTicketTable([
      'ticket_id' => $this->id,
      'location' => $this->location,
      'vorname'   => trim($row['vorname']),
      'nachname'  => trim($row['nachname']),
      'course'  => trim($row['massnahme']),
      'email'  => trim($row['email']),
      'notes_participant'  => trim($row['bemerkung']),
      'password'  =>  $this->generateRandomString(5),
      'branch'  => trim($row['branch']),
      'kurs'  => trim($row['kurs']),
      'deaktivierungsdatum'  => trim($row['deaktivierungsdatum']),
      'gruppe'  => trim($row['gruppe']),
    ]);
  }
}
