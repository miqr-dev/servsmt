<?php

use App\Massnahme;
use Illuminate\Database\Seeder;

class MassnahmeSeeder extends Seeder
{
  public function run()
  {
    $massnahmen = [
      'APM | Arbeitsvorbereitung und Potenzialcoaching',
      'APO | Arbeits- und Potenzialanalyse',
      'BAMF - Alphabeti.-Kurs | BAMF-Alphabetisierungskurs',
      'BAMF - Zweitschrift.-Kurs | BAMF-Zweitschriftenlernende-Kurs',
      'BAMF | Allgemeiner Integrationskurs',
      'BAMF | Berufssprachkurs',
      'bbU | Betreute betriebliche Umschulung',
      'BC - Einzel. | Bewerbungstraining - Einzelcoaching',
      'BM | Bewerbungsmanagement',
      'BT-S | Berufliches Training und Stabilisierung',
      'BUS | Beratung, Unterstützung, Psychologische Stabilisierung',
      'DiB | Deutsch im Beruf',
      'FK LL | Vorbereitung auf Externenprüfung - Fachkraft für Lagerlogistik',
      'FOSI | Feststellung zur Orientierung, Schulung und Integration',
      'Gruko | Erwerb von Grundkompetenzen',
      'IBO | Integration und Berufliche Optimierung im betriebswirtschaftlichen Bereich (Büro)',
      'IK | Umschulung Industriekaufmann/-frau Plus',
      'IK oH | Umschulung Industriekaufmann/-frau oH',
      'IMK | Individuelle Maßnahmekombination KOMPAKT',
      'INT | Interessenten',
      'ISO | Integration, Schulung & Orientierung',
      'ISO Kompakt | Integration, Schulung und Orientierung Kompakt',
      'K E-Com | Umschulung Kaufmann/-frau im E-Commerce Plus',
      'K E-Com o.H. | Umschulung Kaufmann/-frau im E-Commerce o.H.',
      'KBM | Umschulung Kaufmann/-frau für Büromanagement Plus',
      'KBM oH | Umschulung Kaufmann/-frau für Büromanagement oH',
      'KbQ | Kaufmännische betriebswirtschaftliche Qualifikation',
      'kbQ oH | Kaufmännische betriebswirtschaftliche Qualifikation oH',
      'kbQ-M | Kaufmännisch betriebswirtschaftliche Qualifikation für Migranten',
      'KiG | Umschulung Kaufmann/-frau im Gesundheitswesen Plus',
      'KiG oH | Umschulung Kaufmann/-frau im Gesundheitswesen oH',
      'KMQ | Kaufmännische Modulare Qualifikation',
      'KMQ DRV | Kaufmännische Modulare Qualifikation DRV',
      'KMQ DRV B | Kaufmännische Modulare Qualifikation DRV Berlin',
      'KMQ online | Kaufmännische Modulare Qualifikation online',
      'KMQ online Selbstzahler | KMQ online Selbstzahler',
      'MAPO | Migranten in Arbeits- und Potenzialanalyse',
      'MIBO | Migranten in Integration und beruflicher Optimierung',
      'MISO | Migranten in Integration, Schulung & Orientierung',
      'MISO | Migranten in Integration, Schulung und Orientierung_ALT',
      'MISO Kompakt | Migranten in Integration, Schulung und Orientierung Kompakt',
      'MWe | Modulare Weiterbildung Fachkraft Wach- und Sicherheitsdienst',
      'MWe | Modulare Weiterbildung im Gesundheitsbereich',
      'MWe online | Modulare Weiterbildung im Gesundheitsbereich online',
      'MWe online Selbstzahler | Modulare Weiterbildung online Selbstzahler',
      'OSI | Orientierung, Schulung und Integration',
      'PAA | Potenzialcoaching, Aktivierung und Arbeitserprobung',
      'RVL intensiv | Reha-Vorbereitungslehrgang intensiv',
      'RVL US | Reha-Vorbereitungslehrgang für die kaufmännische Umschulung Plus',
      'Stabi | Nachbetreuung und Stabilisierung',
      'ubH | Umschulungsbegleitende Hilfen',
      'VL bbU | Vorbereitungslehrgang für betreute betriebliche Umschulung',
    ];

    foreach ($massnahmen as $name) {
      Massnahme::create(['name' => $name]);
    }
  }
}
