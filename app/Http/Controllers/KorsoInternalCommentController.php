<?php

namespace App\Http\Controllers;

use App\KorsoInternalComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KorsoInternalCommentController extends Controller
{
  public function store(Request $request)
  {
    $request->validate([
      'korso_id' => 'required|exists:korsos,id',
      'comment' => 'required|string|max:1000',
    ]);

    $comment = KorsoInternalComment::create([
      'korso_id' => $request->korso_id,
      'user_id' => Auth::id(),
      'comment' => $request->comment,
    ]);

    return response()->json([
      'id' => $comment->id,
      'user' => Auth::user()->name,
      'comment' => $comment->comment,
      'created_at' => $comment->created_at->diffForHumans(),
    ]);
  }

  public function update(Request $request, KorsoInternalComment $internalComment)
  {
    if ($internalComment->user_id !== Auth::id()) {
      return response()->json(['error' => 'Unauthorized'], 403);
    }

    $request->validate(['comment' => 'required|string|max:1000']);
    $internalComment->update(['comment' => $request->comment]);

    return response()->json(['message' => 'Updated successfully']);
  }


  public function softDelete(KorsoInternalComment $internalComment)
  {
    if ($internalComment->user_id !== Auth::id()) {
      return response()->json(['error' => 'Unauthorized'], 403);
    }

    $internalComment->update(['is_deleted' => true]);

    return response()->json(['message' => 'Marked as deleted']);
  }


  public function restore(KorsoInternalComment $internalComment)
  {
    if ($internalComment->user_id !== Auth::id()) {
      return response()->json(['error' => 'Unauthorized'], 403);
    }

    $internalComment->update(['is_deleted' => false]);

    return response()->json(['message' => 'Comment restored']);
  }
}
