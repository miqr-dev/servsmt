<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** Übungsfirmen Excel export (PracticeCompanyController@export, Super_Admin only). */
class PracticeCompaniesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $companies;

    public function __construct($companies)
    {
        $this->companies = $companies;
    }

    public function collection()
    {
        return $this->companies->map(fn ($company) => [
            optional($company->place)->pnname,
            $company->Windows_Username,
            $company->Windows_Password,
            $company->Email_Username,
            $company->Email_Password,
            $company->Lexware_Username,
            $company->Lexware_Password,
        ]);
    }

    public function headings(): array
    {
        return [
            'Standort',
            'Windows-Benutzer',
            'Windows-Passwort',
            'E-Mail',
            'E-Mail-Passwort',
            'Lexware-Benutzer',
            'Lexware-Passwort',
        ];
    }
}
