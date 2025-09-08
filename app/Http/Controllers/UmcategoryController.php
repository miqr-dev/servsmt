<?php

namespace App\Http\Controllers;

use App\Umcategory;
use Illuminate\Http\Request;

class UmcategoryController extends Controller
{
  public function store(Request $request)
  {
    $request->validate(['name' => 'required|string|unique:categories,name']);

    $category = Umcategory::create($request->all());

    return response()->json($category);
  }
  public function edit(Umcategory $umcategory)
  {
    return view('categories.edit', compact('category'));
  }
  public function update(Request $request, $categoryId)
  {
    $category = Umcategory::findOrFail($categoryId);
    $data = $request->validate(['name' => 'required|string|max:255']);
    $category->update($data);

    return redirect()->route('umfrages.index')->with('success', 'Kategorie aktualisiert.');
  }
  public function destroy($id)
  {
    $umcategory = Umcategory::findOrFail($id);

    // Check if the category is associated with any Umfrages
    if ($umcategory->umfrages()->count() > 0) {
      // If it is associated, redirect back with an error message.
      return redirect()->back()->with('error', 'Kategorie kann nicht gelöscht werden, da sie mit Linken verknüpft ist.');
    }

    // If it is not associated, proceed with deletion.
    $umcategory->delete();

    return redirect()->route('umfrages.index')->with('success', 'Kategorie erfolgreich gelöscht.');
  }
}
