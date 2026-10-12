<?php

namespace App\Http\Controllers;

use App\Exports\PracticeCompaniesExport;
use App\Place;
use App\PracticeCompany;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Übungsfirmen (/practice-companies, PracticeCompanies/Index.vue, modernized 2026-10-12).
 *
 * Access data of the practice companies per Standort, in two lists that share
 * the table practice_companies:
 *   - "windows": Windows login + e-mail account (old page /practice-companies/city/{city})
 *   - "lexware": Lexware login                  (old page /practice-companies/lex/{city})
 * A row shows in a list when it has data for that list; editing or deleting in
 * one list never touches the other list's fields (a row with both kinds of
 * data is only deleted when nothing is left).
 *
 * Viewing: role Terminal (route_access 'practice-companies*'), Super_Admin.
 * Adding, changing, deleting and the Excel export: Super_Admin only (decision
 * 2026-10-12; before, the server accepted changes from Terminal too, only the
 * old page hid the buttons). The never-saved "Aktuell" column was dropped.
 */
class PracticeCompanyController extends Controller
{
    /** list => its columns */
    public const KINDS = [
        'windows' => ['Windows_Username', 'Windows_Password', 'Email_Username', 'Email_Password'],
        'lexware' => ['Lexware_Username', 'Lexware_Password'],
    ];

    public function index(Request $request)
    {
        $canEdit = (bool) $request->user()?->isSuperAdmin();
        $companies = PracticeCompany::orderBy('id')->get();
        $used = $companies->pluck('place_id')->unique()->all();
        $places = Place::orderBy('pnname')->get(['id', 'pnname'])
            ->map(fn (Place $p) => ['id' => $p->id, 'name' => (string) $p->pnname, 'used' => in_array($p->id, $used)]);

        return Inertia::render('PracticeCompanies/Index', [
            'companies' => $companies->map(fn (PracticeCompany $c) => [
                'id' => $c->id,
                'place_id' => $c->place_id,
                'Windows_Username' => (string) $c->Windows_Username,
                'Windows_Password' => (string) $c->Windows_Password,
                'Email_Username' => (string) $c->Email_Username,
                'Email_Password' => (string) $c->Email_Password,
                'Lexware_Username' => (string) $c->Lexware_Username,
                'Lexware_Password' => (string) $c->Lexware_Password,
            ])->values(),
            // tabs: Standorte with entries; Super_Admin may start a new one
            'cities' => $places->where('used', true)->values(),
            'otherCities' => $canEdit ? $places->where('used', false)->values() : [],
            'canEdit' => $canEdit,
        ]);
    }

    /** old sidebar links / bookmarks */
    public function viewByCity($city)
    {
        return redirect()->route('practice-companies.index', ['stadt' => $city, 'art' => 'windows']);
    }

    public function viewByLex($city)
    {
        return redirect()->route('practice-companies.index', ['stadt' => $city, 'art' => 'lexware']);
    }

    public function store(Request $request)
    {
        $this->authorizeEdit($request);
        $data = $request->validate([
            'place_id' => ['required', 'integer', Rule::exists('places', 'id')],
            'kind' => ['required', Rule::in(array_keys(self::KINDS))],
        ]);
        $fields = $this->fields($request, $data['kind']);

        PracticeCompany::create(['place_id' => $data['place_id']] + $fields);

        return back()->with('success', 'Eintrag gespeichert.');
    }

    public function update(Request $request, PracticeCompany $practiceCompany)
    {
        $this->authorizeEdit($request);
        $kind = $request->validate(['kind' => ['required', Rule::in(array_keys(self::KINDS))]])['kind'];

        $practiceCompany->update($this->fields($request, $kind));

        return back()->with('success', 'Änderungen gespeichert.');
    }

    /** ?kind=windows|lexware clears that list's fields; the row goes when nothing is left */
    public function destroy(Request $request, PracticeCompany $practiceCompany)
    {
        $this->authorizeEdit($request);
        $kind = $request->input('kind');

        if (isset(self::KINDS[$kind])) {
            $practiceCompany->fill(array_fill_keys(self::KINDS[$kind], null));
            $left = collect(self::KINDS)->flatten()->contains(fn ($f) => trim((string) $practiceCompany->{$f}) !== '');
            $left ? $practiceCompany->save() : $practiceCompany->delete();
        } else {
            $practiceCompany->delete();
        }

        return back()->with('success', 'Eintrag gelöscht.');
    }

    /** ?city=Leipzig (Place pnname) or all Standorte */
    public function export(Request $request)
    {
        $this->authorizeEdit($request);
        $query = PracticeCompany::with('place')->orderBy('place_id')->orderBy('id');
        $city = trim((string) $request->query('city'));
        if ($city !== '') {
            $query->where('place_id', Place::where('pnname', $city)->firstOrFail()->id);
        }
        $name = 'uebungsfirmen_'.($city !== '' ? \Illuminate\Support\Str::slug($city).'_' : '').now()->format('Y-m-d').'.xlsx';

        return Excel::download(new PracticeCompaniesExport($query->get()), $name);
    }

    private function authorizeEdit(Request $request): void
    {
        abort_unless((bool) $request->user()?->isSuperAdmin(), 403, 'Nur Super_Admin darf Übungsfirmen ändern.');
    }

    /** the list's fields from the request, trimmed, '' -> null; at least one must be filled */
    private function fields(Request $request, string $kind): array
    {
        $rules = array_fill_keys(self::KINDS[$kind], ['nullable', 'string', 'max:255']);
        $data = $request->validate($rules);
        $out = [];
        foreach (self::KINDS[$kind] as $f) {
            $v = trim((string) ($data[$f] ?? ''));
            $out[$f] = $v === '' ? null : $v;
        }
        if (! array_filter($out)) {
            throw ValidationException::withMessages([self::KINDS[$kind][0] => 'Bitte mindestens ein Feld ausfüllen.']);
        }

        return $out;
    }
}
