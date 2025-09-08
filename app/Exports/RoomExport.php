<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class RoomExport implements FromArray
{
    public $importRoom;


    public function __construct($importRoom)
    {
      $this->importRoom = $importRoom;
    }


    public function array(): array
    {
        return $this->importRoom;
    }
}
