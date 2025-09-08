<?php

namespace App\Http\Controllers;

use App\ZertifizierungItem;
use Illuminate\Http\Request;

class ZertifizierungItemController extends Controller
{
  public function index()
  {
    $items = ZertifizierungItem::all();
    return view('korso.zertifizierung_items.index', compact('items'));
  }

  public function create()
  {
    return view('korso.zertifizierung_items.create');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
    ]);

    ZertifizierungItem::create([
      'name' => $validated['name'],
      'location_needed' => $request->has('location_needed'),
      'massnahme_needed' => $request->has('massnahme_needed'),
    ]);

    return redirect()->route('zertifizierung_items.index')->with('success', 'Eintrag erfolgreich erstellt.');
  }

  public function edit($id)
  {
    $item = ZertifizierungItem::findOrFail($id);
    return view('korso.zertifizierung_items.edit', compact('item'));
  }

  public function update(Request $request, ZertifizierungItem $zertifizierung_item)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
    ]);

    $zertifizierung_item->update([
      'name' => $validated['name'],
      'location_needed' => $request->has('location_needed'),
      'massnahme_needed' => $request->has('massnahme_needed'),
    ]);

    return redirect()->route('zertifizierung_items.index')->with('success', 'Eintrag erfolgreich aktualisiert.');
  }

  public function destroy(ZertifizierungItem $zertifizierung_item)
  {
    $zertifizierung_item->delete();

    return redirect()->route('zertifizierung_items.index')
      ->with('success', 'Option erfolgreich gelöscht.');
  }
}
