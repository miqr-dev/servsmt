<?php

namespace App\Http\Controllers;

use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\TicketSpecialComment;

class SpecialTicketController extends Controller
{
  public function index(Request $request)
  {
    $user = Auth::user();

    if ($user->id !== 16 && !$user->hasRole('Super_Admin')) {
      abort(403, 'Unauthorized action.');
    }

    // Define & sort city list alphabetically (proper German locale sort)
    $cities = ['Dresden', 'Chemnitz', 'Berlin', 'Leipzig', 'Döbeln', 'Riesa'];
    setlocale(LC_COLLATE, 'de_DE.UTF-8');
    usort($cities, function ($a, $b) {
      return strcoll($a, $b);
    });

    $selectedCity = $request->query('city');

    $tickets = Ticket::query()
      ->whereHas('subUser', function ($q) use ($cities) {
        $q->whereIn('ort', $cities);
      })
      ->with(['subUser', 'ticket_status', 'invitem', 'specialComments.author'])
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('tickets.special.index', [
      'tickets' => $tickets,
      'user'    => $user,
      'cities'  => $cities,
    ]);
  }

  private function canEditSpecialComment($user): bool
  {
    return $user && ($user->id === 16 || $user->hasRole('Super_Admin'));
  }
  // list comments for a ticket
  public function listSpecialComments(Ticket $ticket): JsonResponse
  {
    $comments = $ticket->specialComments()
      ->with(['author:id,name,username'])
      ->get()
      ->map(function ($c) {
        return [
          'id' => $c->id,
          'body' => $c->body,
          'author' => $c->author->name ?? $c->author->username ?? ('User#' . $c->user_id),
          'author_id' => $c->user_id,
          'created_at' => $c->created_at->toIso8601String(),
          'created_human' => $c->created_at->diffForHumans(),
          'updated_at' => $c->updated_at->toIso8601String(),
        ];
      });

    return response()->json(['ok' => true, 'comments' => $comments]);
  }

  // create
  public function storeSpecialComment(Ticket $ticket, Request $request): JsonResponse
  {
    $user = Auth::user();
    if (!$this->canEditSpecialComment($user)) {
      return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
    }

    $data = $request->validate(['body' => 'required|string']);
    $c = TicketSpecialComment::create([
      'ticket_id' => $ticket->id,
      'user_id'   => $user->id,
      'body'      => $data['body'],
    ]);

    $author = $user->name ?? $user->username ?? ('User#' . $user->id);

    return response()->json([
      'ok' => true,
      'comment' => [
        'id' => $c->id,
        'body' => $c->body,
        'author' => $author,
        'author_id' => $user->id,
        'created_at' => $c->created_at->toIso8601String(),
        'created_human' => $c->created_at->diffForHumans(),
      ]
    ]);
  }

  public function updateSpecialComment(Ticket $ticket, TicketSpecialComment $sc, Request $request): \Illuminate\Http\JsonResponse
  {
    $user = Auth::user();

    // still require the role to CREATE comments, but for UPDATE you must be the author
    if (!$this->canEditSpecialComment($user)) {
      return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
    }
    if ($sc->ticket_id !== $ticket->id) {
      return response()->json(['ok' => false, 'message' => 'Invalid ticket'], 422);
    }
    // 👇 only the author can edit
    if ($sc->user_id !== $user->id) {
      return response()->json(['ok' => false, 'message' => 'Nur der Verfasser darf diesen Kommentar bearbeiten.'], 403);
    }

    $data = $request->validate(['body' => 'required|string']);
    $sc->body = $data['body'];
    $sc->save();

    $author = optional($sc->author)->name ?? optional($sc->author)->username ?? ('User#' . $sc->user_id);

    return response()->json([
      'ok' => true,
      'comment' => [
        'id' => $sc->id,
        'body' => $sc->body,
        'author' => $author,
        'author_id' => $sc->user_id,
        'created_at' => $sc->created_at->toIso8601String(),
        'created_human' => $sc->created_at->diffForHumans(),
        'updated_at' => $sc->updated_at->toIso8601String(),
      ]
    ]);
  }

  // DELETE
  public function destroySpecialComment(Ticket $ticket, TicketSpecialComment $sc): \Illuminate\Http\JsonResponse
  {
    $user = Auth::user();

    if (!$this->canEditSpecialComment($user)) {
      return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
    }
    if ($sc->ticket_id !== $ticket->id) {
      return response()->json(['ok' => false, 'message' => 'Invalid ticket'], 422);
    }
    // 👇 only the author can delete
    if ($sc->user_id !== $user->id) {
      return response()->json(['ok' => false, 'message' => 'Nur der Verfasser darf diesen Kommentar löschen.'], 403);
    }

    $sc->delete();
    return response()->json(['ok' => true, 'message' => 'Deleted']);
  }
}
