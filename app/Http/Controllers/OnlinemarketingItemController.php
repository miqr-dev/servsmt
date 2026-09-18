<?php

namespace App\Http\Controllers;

use App\OnlinemarketingItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnlinemarketingItemController extends Controller
{
  public function index()
  {
    $items = OnlinemarketingItem::all();
    return Inertia::render('Korso/OnlinemarketingItems/Index', [
      'items' => $items,
    ]);
  }

  public function create()
  {
    return Inertia::render('Korso/OnlinemarketingItems/Create');
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
    ]);

    OnlinemarketingItem::create($request->only('name'));

    return redirect()->route('onlinemarketing_items.index')
      ->with('success', 'Option erfolgreich hinzugefügt.');
  }

  public function edit($id)
  {
    $item = OnlinemarketingItem::findOrFail($id);
    return Inertia::render('Korso/OnlinemarketingItems/Edit', [
      'item' => $item,
    ]);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:255',
    ]);

    $item = OnlinemarketingItem::findOrFail($id);
    $item->update($request->only('name'));

    return redirect()->route('onlinemarketing_items.index')
      ->with('success', 'Option erfolgreich aktualisiert.');
  }

  public function destroy($id)
  {
    $item = OnlinemarketingItem::findOrFail($id);
    $item->delete();

    return redirect()->route('onlinemarketing_items.index')
      ->with('success', 'Option erfolgreich gelöscht.');
  }
}
