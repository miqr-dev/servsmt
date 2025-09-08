<?php

use App\ZertifizierungItem;
use Illuminate\Database\Seeder;

class ZertifizierungItemSeeder extends Seeder
{
    public function run()
    {
       $items = [
            [
                'name' => 'Beantragung einer Maßnahmenummer',
                'location_needed' => true,
                'massnahme_needed' => true,
            ],
            [
                'name' => 'Erstellung eines individuellen Kurzkonzepts/Kostenangebots',
                'location_needed' => true,
                'massnahme_needed' => true,
            ],
            [
                'name' => 'Anforderung eines Maßnahmebogens/Certqua-Zertifikats',
                'location_needed' => true,
                'massnahme_needed' => true,
            ],
            [
                'name' => 'Erstellung von Verkaufsartikeln/Kursen im IS+',
                'location_needed' => true,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'BAMF-Angelegenheiten',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Ausschreibungen',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Fehlermeldung/Aktualisierung Kurzkonzept',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Fehlermeldung/Aktualisierung IS+',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Fehlermeldung/Aktualisierung Checklisten',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Fehlermeldung/Aktualisierung Infolaufwerk',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Fehlermeldung/Aktualisierung InfoNet',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Fehlermeldung/Aktualisierung Kursnet',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Fehlermeldung/Aktualisierung Schulungslaufwerk',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
            [
                'name' => 'Sonstige Anliegen',
                'location_needed' => false,
                'massnahme_needed' => false,
            ],
        ];

        foreach ($items as $item) {
            ZertifizierungItem::create($item);
        }
    }
}
