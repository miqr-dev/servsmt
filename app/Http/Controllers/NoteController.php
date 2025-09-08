<?php

namespace App\Http\Controllers;

use App\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
  public function update(Request $request, Note $note)
  {
    $note->content = $request->input('content');
    $note->save();

    return response()->json(['message' => 'Note updated successfully']);
  }

  public function store(Request $request)
  {
    $note = new Note();
    $note->content = $request->content;
    $note->place_id = $request->place_id; // Make sure you have the place_id field in your notes table

    $note->save();

    return response()->json([
      'message' => 'Note added successfully',
      'note' => $note
    ]);
  }

      public function destroy(Note $note)
    {
        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
