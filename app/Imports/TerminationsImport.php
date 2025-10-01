<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Termination;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class TerminationsImport implements ToModel, WithStartRow
{


    public function model(array $row)
    {
        return new Termination([
            'name'        =>  $row[0],
            'location'    =>  $row[1],
            'occupation'  =>  $row[2],
            'exit'        =>  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[3]))->format('Y-m-d'),
            'is_active'   =>  true,

        ]);
    }

     public function startRow(): int
    {
        return 2;
    }
}
