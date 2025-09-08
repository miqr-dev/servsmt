<?php

namespace App\Http\Controllers;

use App\OnlinemarketingItem;
use Illuminate\Http\Request;

class OnlinemarketingItemController extends Controller
{
  public function index()
  {
    $items = OnlinemarketingItem::all();
    return view('korso.onlinemarketing_items.index', compact('items'));
  }

  public function create()
  {
    return view('korso.onlinemarketing_items.create');
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
    return view('korso.onlinemarketing_items.edit', compact('item'));
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
