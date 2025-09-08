<?php


namespace App\Exports;

use App\PracticeCompany;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PracticeCompaniesExport implements FromCollection, WithHeadings
{
    protected $companies;

    public function __construct($companies)
    {
        $this->companies = $companies;
    }

    public function collection()
    {
        return $this->companies->map(function($company) {
            return [
                $company->Windows_Username,
                $company->Windows_Password,
                $company->Lexware_Username,
                $company->Lexware_Password,
                $company->Email_Username,
                $company->Email_Password,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Windows Username',
            'Windows Password',
            'Lexware Username',
            'Lexware Password',
            'Email Username',
            'Email Password',
        ];
    }
}

