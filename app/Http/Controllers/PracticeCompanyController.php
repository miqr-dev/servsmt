<?php

namespace App\Http\Controllers;

use App\Place;
use App\PracticeCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PracticeCompaniesExport;

class PracticeCompanyController extends Controller
{
  public function index()
  {
    $companies = PracticeCompany::all();
    $user = Auth::user();
    $isSuperAdmin = $user->hasRole('Super_Admin');

    return view('practice_companies.index', compact('companies', 'isSuperAdmin'));
  }

  public function viewByCity($city)
  {
    $place = Place::where('pnname', $city)->firstOrFail();
    $companies = PracticeCompany::where('place_id', $place->id)->get();
    $user = Auth::user();
    $isSuperAdmin = $user->hasRole('Super_Admin');

    return view('practice_companies.city', compact('companies', 'isSuperAdmin', 'place'));
  }

  public function viewByLex($city)
  {
    $place = Place::where('pnname', $city)->firstOrFail();
    $companies = PracticeCompany::where('place_id', $place->id)->get();
    $user = Auth::user();
    $isSuperAdmin = $user->hasRole('Super_Admin');

    return view('practice_companies.lex', compact('companies', 'isSuperAdmin', 'place'));
  }
  public function store(Request $request)
  {
    $request->validate([
      'place_id' => 'required',
      'Windows_Username' => '',
      'Windows_Password' => '',
      'Lexware_Username' => '',
      'Lexware_Password' => '',
      'Email_Username' => '',
      'Email_Password' => '',
      'aktuelld' => '',
    ]);

    $company = PracticeCompany::create($request->all());

    return response()->json(['success' => true, 'company' => $company]);
  }

  public function update(Request $request, PracticeCompany $practiceCompany)
  {
    $request->validate([
      'place_id' => 'required',
      'Windows_Username' => '',
      'Windows_Password' => '',
      'Lexware_Username' => '',
      'Lexware_Password' => '',
      'Email_Username' => '',
      'Email_Password' => '',
      'aktuell' => '',
    ]);

    $practiceCompany->update($request->all());

    return response()->json(['success' => true, 'company' => $practiceCompany]);
  }

  public function destroy(PracticeCompany $practiceCompany)
  {
    $practiceCompany->delete();

    return response()->json(['success' => true]);
  }

  public function export(Request $request)
  {
    $city = $request->query('city');
    if ($city) {
      $place = Place::where('pnname', $city)->firstOrFail();
      $companies = PracticeCompany::where('place_id', $place->id)->get();
    } else {
      $companies = PracticeCompany::all(); // fallback, export all companies if no city is provided
    }

    return Excel::download(new PracticeCompaniesExport($companies), 'practice_companies.xlsx');
  }
}
