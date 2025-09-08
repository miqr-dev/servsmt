<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;



class UserExport implements FromArray,WithHeadings
{
    public $importuser;


    public function __construct($importUser)
    {
      $this->importUser = $importUser;
    }


    public function array(): array
    {
        return $this->importUser;
    }

    public function headings(): array
    {
        return [
            'samaccountname',
            'title',       //position
            'department', //abteilung
            'telephoneNumber', //tel
            'facsimileTelephoneNumber', //fax
            'l', //ort
            'streetAddress', 
            'postalCode',
            'personalTitle',    //Herr , Frau
            'givenName',   //first name
            'sn',             // 1 last name 
            'mobile',
            'otherHomePhone',
            'url',
            'info',
            'physicalDeliveryOfficeName',
        ];
    }
}
