<?php

namespace App\Http\Controllers;

use App\Place;
use App\Umcategory;
use App\Umfrage;
use Illuminate\Http\Request;

class UmfrageController extends Controller
{

  public function index()
  {
    $places = Place::all(); // Assuming you have a Place model
    return view('umfrages.index', compact('places'));
  }

  public function getByPlace($placeId)
  {
    $place = Place::findOrFail($placeId);
    $umfrages = $place->umfrages()->with('umcategory')->get();

    return view('umfrages.partials.umfrages', compact('umfrages'));
  }


  public function create()
  {
    $places = Place::all();
    $categories = Umcategory::all();
    return view('umfrages.create', compact('places', 'categories'));
  }
  public function store(Request $request)
  {
    $data = $request->validate([
      'place_id' => 'required|exists:places,id',
      'title' => 'required|string|max:255',
      'url' => 'required',
      'umcategory_id' => 'required|exists:umcategories,id', // Ensure this line is correct according to your categories table

    ]);

    // Prepend "https://" to the URL if it's not already present
    if (!preg_match('~^(?:f|ht)tps?://~i', $data['url'])) {
      $data['url'] = "https://" . $data['url'];
    }

    Umfrage::create($data);

    return redirect()->route('umfrages.index')->with('success', 'Umfrage added successfully.');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  public function edit(Umfrage $umfrage)
  {
    $places = Place::all();
    $categories = Umcategory::all();
    return view('umfrages.edit', compact('umfrage', 'places', 'categories'));
  }

  public function update(Request $request, Umfrage $umfrage)
  {
    $data = $request->validate([
      'place_id' => 'required|exists:places,id',
      'title' => 'required|string|max:255',
      'url' => 'required|url',
      'umcategory_id' => 'required|exists:umcategories,id',
    ]);

    $umfrage->update($data);

    return redirect()->route('umfrages.index')->with('success', 'Umfrage erfolgreich aktualisiert.');
  }
  public function destroy($id)
  {
    $umfrage = Umfrage::findOrFail($id);

    // Perform any checks if necessary, such as ensuring the Umfrage can be deleted

    $umfrage->delete();

    return redirect()->route('umfrages.index')->with('success', 'Umfrage erfolgreich gelöscht.');
  }
}
