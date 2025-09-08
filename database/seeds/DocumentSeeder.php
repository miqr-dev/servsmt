<?php

use App\Document;
use App\DocumentVariable;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
  public function run()
  {
    $overview = Document::create(['name' => 'überblick_ef', 'bundesland' => 'Thüringen', 'city' => 'Erfurt']);
    $anschreiben = Document::create(['name' => 'anschreiben_frau_gräbner', 'bundesland' => 'Thüringen', 'city' => 'Erfurt']);
    $anmeldungUmschulung = Document::create(['name' => 'anmeldung_einer_umschulung', 'bundesland' => 'Thüringen', 'city' => 'Erfurt']);
    $ueberblickShl = Document::create(['name' => 'überblick_shl', 'bundesland' => 'Thüringen', 'city' => 'Suhl']);
    $überblick_lpz = Document::create(['name' => 'überblick_lpz', 'bundesland' => 'Sachsen', 'city' => 'Leipzig']);
    $praktischeVermittlung = Document::create(['name' => '07_praktische_vermittlung', 'bundesland' => 'Thüringen', 'city' => 'Erfurt']);
    $praktischeVermittlungLpz = Document::create(['name' => '07_praktische_vermittlung_lpz', 'bundesland' => 'Sachsen', 'city' => 'Leipzig']);
    $überblick_dd = Document::create(['name' => 'überblick_dd', 'bundesland' => 'Sachsen', 'city' => 'Dresden']);
    $anschreibenReich = Document::create(['name' => 'anschreiben_Reich', 'bundesland' => 'Sachsen', 'city' => 'Dresden']);
    $anschreibenNeumann = Document::create(['name' => 'anschreiben_Neumann', 'bundesland' => 'Sachsen', 'city' => 'Chemnitz']);
    $praktischeVermittlungDd = Document::create(['name' => '07_praktische_vermittlung_dd', 'bundesland' => 'Sachsen', 'city' => 'Dresden']);
    $praktischeVermittlungCh = Document::create(['name' => 'praktische_vermittlung_ch', 'bundesland' => 'Sachsen', 'city' => 'Chemnitz']);







    $variables = [
      'tagesaktuellesDatum' => '14.05.2024',
      'maßnahmebeginn' => '09.07.2024',
      'maßnahmeende' => '08.07.2026',
      'stunden_übungsbüro' => '992',
      'praktikumsbeginn' => '23.04.2026',
      'praktikumsende' => '03.07.2026',
      'spezialisierungStart' => '06.10.2025',
      'spezialisierungEnd' => '23.02.2026',
    ];

    foreach ($variables as $key => $value) {
      DocumentVariable::create([
        'document_id' => $overview->id,
        'key' => $key,
        'value' => $value,
      ]);

      DocumentVariable::create([
        'document_id' => $ueberblickShl->id,
        'key' => $key,
        'value' => $value,
      ]);

      DocumentVariable::create([
        'document_id' => $anmeldungUmschulung->id,
        'key' => 'maßnahmebeginn',
        'value' => '09.07.2024',
      ]);

      DocumentVariable::create([
        'document_id' => $anmeldungUmschulung->id,
        'key' => 'maßnahmeende',
        'value' => '08.07.2026',
      ]);

      // DocumentVariable::create([
      //     'document_id' => $ueberblickLpz->id,
      //     'key' => $key,
      //     'value' => $value,
      // ]);
    }

    // Specific variables for the new Dresden document
    DocumentVariable::create(['document_id' => $überblick_dd->id, 'key' => 'maßnahmebeginn', 'value' => '09.07.2024']);
    DocumentVariable::create(['document_id' => $überblick_dd->id, 'key' => 'maßnahmeende', 'value' => '08.07.2026']);
    DocumentVariable::create(['document_id' => $überblick_dd->id, 'key' => 'stunden_außerbetriebliche_ausbildungsstätte', 'value' => '1616']);


    DocumentVariable::create(['document_id' => $anschreiben->id, 'key' => 'tagesaktuellesDatum', 'value' => '14.05.2024']);
    DocumentVariable::create(['document_id' => $praktischeVermittlung->id, 'key' => 'maßnahmebeginn', 'value' => '09.07.2024']);

    DocumentVariable::create([
      'document_id' => $praktischeVermittlung->id,
      'key' => 'maßnahmeende',
      'value' => '08.07.2026',
    ]);

    DocumentVariable::create([
      'document_id' => $praktischeVermittlung->id,
      'key' => 'praktikumsbeginn',
      'value' => '23.04.2026',
    ]);
    DocumentVariable::create([
      'document_id' => $praktischeVermittlung->id,
      'key' => 'praktikumsende',
      'value' => '23.04.2028',
    ]);
    DocumentVariable::create([
      'document_id' => $praktischeVermittlung->id,
      'key' => 'hol_beginn',
      'value' => '23.04.2000',
    ]);
    DocumentVariable::create([
      'document_id' => $praktischeVermittlung->id,
      'key' => 'hol_ende',
      'value' => '23.04.2001',
    ]);
    DocumentVariable::create(['document_id' => $überblick_lpz->id, 'key' => 'maßnahmebeginn', 'value' => '08.07.2026']);
    DocumentVariable::create(['document_id' => $überblick_lpz->id, 'key' => 'maßnahmeende', 'value' => '08.07.2026']);
    DocumentVariable::create(['document_id' => $überblick_lpz->id, 'key' => 'stunden_praktlische_ausbildung', 'value' => '1704']);
    // DocumentVariable::create(['document_id' => $ueberblickShl->id, 'key' => 'hlWQ1', 'value' => '168']);
    // DocumentVariable::create(['document_id' => $ueberblickShl->id, 'key' => 'bsWQ1', 'value' => '336']);
    // DocumentVariable::create(['document_id' => $ueberblickShl->id, 'key' => 'hlWQ2', 'value' => '168']);
    // DocumentVariable::create(['document_id' => $ueberblickShl->id, 'key' => 'bsWQ2', 'value' => '336']);
    // DocumentVariable::create(['document_id' => $overview->id, 'key' => 'hlWQ', 'value' => '336']);
    // DocumentVariable::create(['document_id' => $overview->id, 'key' => 'bsWQ', 'value' => '672']);



    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'maßnahmebeginn', 'value' => '09.07.2024']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'maßnahmeende', 'value' => '08.07.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'hol1_beginn', 'value' => '07.05.2025']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'hol1_ende', 'value' => '08.10.2025']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'hol2_beginn', 'value' => '22.10.2025']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'hol2_ende', 'value' => '25.03.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'praktikumsbeginn', 'value' => '30.04.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungLpz->id, 'key' => 'praktikumsende', 'value' => '07.07.2026']);
    DocumentVariable::create(['document_id' => $anschreibenReich->id, 'key' => 'tagesaktuellesDatum', 'value' => '14.05.2024']);
    DocumentVariable::create(['document_id' => $anschreibenNeumann->id, 'key' => 'tagesaktuellesDatum', 'value' => '14.05.2024']);



    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'tagesaktuellesDatum', 'value' => '14.05.2024']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'maßnahmebeginn', 'value' => '09.07.2024']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'maßnahmeende', 'value' => '08.07.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'hol1_beginn', 'value' => '07.05.2025']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'hol1_ende', 'value' => '08.10.2025']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'hol2_beginn', 'value' => '22.10.2025']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'hol2_ende', 'value' => '25.03.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'praktikumsbeginn', 'value' => '30.04.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungDd->id, 'key' => 'praktikumsende', 'value' => '07.07.2026']);


    DocumentVariable::create(['document_id' => $praktischeVermittlungCh->id, 'key' => 'maßnahmebeginn', 'value' => '09.07.2024']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungCh->id, 'key' => 'maßnahmeende', 'value' => '08.07.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungCh->id, 'key' => 'praktikumsbeginn', 'value' => '23.04.2026']);
    DocumentVariable::create(['document_id' => $praktischeVermittlungCh->id, 'key' => 'praktikumsende', 'value' => '03.07.2026']);
  }
}
