<?php

namespace App\Http\Controllers;

use App\User;
use App\Korso;
use App\Handwerk;
use App\Ticket;
use Illuminate\Http\Request;
use App\Comment;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Notifications\CommentNotification;
use Illuminate\Support\Facades\Notification;



use App\Support\Notify;

class CommentController extends Controller
{
  public function __construct()
  {
    $this->middleware('web');

    if (Config::get('comments.guest_commenting') == true) {
      $this->middleware('auth')->except('store');
      $this->middleware(ProtectAgainstSpam::class)->only('store');
    } else {
      $this->middleware('auth');
    }
  }

  public function store(Request $request)
  {
    // If guest commenting is turned off, authorize this action.
    if (Config::get('comments.guest_commenting') == false) {
      Gate::authorize('create-comment', Comment::class);
    }

    // Define guest rules if user is not logged in.
    if (! Auth::check()) {
      $guest_rules = [
        'guest_name'  => 'required|string|max:255',
        'guest_email' => 'required|string|email|max:255',
      ];
    }

    // Merge guest rules, if any, with normal validation rules.
    // commentable_id: the Vue CommentThread posts a JSON number, the old Blade
    // form a string - 'string' rejected the number ("Commentable id muss ein
    // string sein"). Any positive integer is fine.
    Validator::make($request->all(), array_merge($guest_rules ?? [], [
      'commentable_type' => 'required|string',
      'commentable_id'   => 'required|integer|min:1',
      'message'          => 'required|string',
    ]))->validate();

    // Only these models can be commented on - the class name comes from the
    // request, so never instantiate anything else from it.
    $type = ltrim((string) $request->commentable_type, '\\');
    abort_unless(in_array($type, [Ticket::class, Handwerk::class, Korso::class], true), 422, 'Ungültiger Kommentar-Typ.');

    // Load the commentable model
    $model = $type::findOrFail((int) $request->commentable_id);

    // Create the comment instance
    $commentClass = Config::get('comments.model');
    $comment      = new $commentClass;

    if (! Auth::check()) {
      $comment->guest_name  = $request->guest_name;
      $comment->guest_email = $request->guest_email;
    } else {
      $comment->commenter()->associate(Auth::user());
    }

    $comment->commentable()->associate($model);
    $comment->comment   = $request->message;
    $comment->approved  = ! Config::get('comments.approval_required');

    // Save first, then notify (2026-10-12: the mails went out before the
    // comment was saved; they are queued now, see App\Support\Notify).
    $comment->save();
    if (Auth::check()) {
      $this->notifyAbout($comment, $model, null);
    }

    // The Vue CommentThread component posts here via axios and expects
    // the created comment back as JSON; the still-Blade Tickets admin
    // page's plain <form> posts here too and expects the classic
    // redirect - branch on what the caller actually wants.
    if ($request->wantsJson()) {
      return response()->json($comment);
    }

    return Redirect::to(URL::previous() . '#comment-' . $comment->getKey());
  }

  public function update(Request $request, Comment $comment)
  {
    Gate::authorize('edit-comment', $comment);

    Validator::make($request->all(), [
      'message' => 'required|string'
    ])->validate();

    $comment->update([
      'comment' => $request->message
    ]);

    if ($request->wantsJson()) {
      return response()->json($comment);
    }

    return Redirect::to(URL::previous() . '#comment-' . $comment->getKey());
  }

  /**
   * Deletes a comment.
   */
  public function destroy(Comment $comment)
  {
    //     Gate::authorize('delete-comment', $comment);

    //     if (Config::get('comments.soft_deletes') == true) {
    // 	$comment->delete();
    // }
    // else {
    // 	$comment->forceDelete();
    // }

    //     return Redirect::back();
  }

  /**
   * Creates a reply "comment" to a comment.
   */
  public function reply(Request $request, Comment $comment)
  {
    Gate::authorize('reply-to-comment', $comment);

    Validator::make($request->all(), [
      'message' => 'required|string'
    ])->validate();

    // Build the reply
    $commentClass = Config::get('comments.model');
    $reply        = new $commentClass;
    $reply->commenter()->associate(Auth::user());
    $reply->commentable()->associate($comment->commentable);
    $reply->parent()->associate($comment);
    $reply->comment  = $request->message;
    $reply->approved = ! Config::get('comments.approval_required');

    $reply->save();
    $this->notifyAbout($reply, $comment->commentable, $comment);

    if ($request->wantsJson()) {
      return response()->json($reply);
    }

    return Redirect::to(URL::previous() . '#comment-' . $reply->getKey());
  }

  /**
   * Comment / reply mails (2026-10-12).
   *
   * Who: the people of the ticket, never the writer.
   * - The submitter wrote: the assignee; nobody assigned -> the team
   *   (IT: all Super_Admin, Handwerk: Handwerk_verwaltung, Korso: all Korso_ma).
   * - Someone else wrote: the submitter (Korso "als Sekretariat": the group
   *   mailbox) and the assignee; Korso without assignee: all Korso_ma too.
   * - A reply also reaches the author of the comment it answers; that person
   *   reads "Antwort auf Ihren Kommentar", the others "neue Antwort".
   * Before, a reply only reached the parent's author (also when that was the
   * writer, who then got a mail about their own reply).
   */
  private function notifyAbout(Comment $comment, $model, ?Comment $parent): void
  {
    if (! $model) {
      return;
    }
    $writer = $comment->commenter;
    $writerId = (int) $comment->commenter_id;
    $submitterId = (int) $model->submitter;
    $assignee = $model->assignedTo ? User::find($model->assignedTo) : null;
    $mailbox = ($model instanceof Korso && $model->sek_group_id && $model->sekGroup) ? $model->sekGroup->email : null;

    $users = collect();
    $toMailbox = false;
    if ($writerId === $submitterId) {
      if ($assignee) {
        $users->push($assignee);
      } elseif ($model instanceof Handwerk) {
        $users = $users->merge(\App\Support\HandwerkResponsibility::verwaltungUsers());
      } elseif ($model instanceof Korso) {
        $users = $users->merge(User::role('Korso_ma')->get());
      } else {
        $users = $users->merge(User::role('Super_Admin')->get());
      }
    } else {
      if ($mailbox) {
        $toMailbox = true;
      } else {
        $users->push(User::find($submitterId));
      }
      if ($assignee) {
        $users->push($assignee);
      } elseif ($model instanceof Korso) {
        $users = $users->merge(User::role('Korso_ma')->get());
      }
    }

    // reply: + the author of the answered comment
    $parentAuthorId = $parent ? (int) $parent->commenter_id : 0;
    $parentIsMailbox = $parent && $mailbox && $parentAuthorId === $submitterId;
    if ($parent && $parentAuthorId && $parentAuthorId !== $writerId) {
      if ($parentIsMailbox) {
        $toMailbox = true;
      } else {
        $users->push(User::find($parentAuthorId));
      }
    }

    $users = $users->filter()->reject(fn ($u) => (int) $u->id === $writerId)->unique('id')->values();

    $base = [
      'title'          => $parent ? 'Kommentarantwort' : 'neuer Kommentar',
      'id'             => $model->getKey(),
      'comment'        => $comment->comment,
      'commenter_id'   => $writer->username ?? '',
      'commenter_name' => $this->personName($writer),
      'type'           => class_basename($model), // Ticket | Handwerk | Korso
      'ticket_title'   => $model->problem_type,
    ];
    $kind = fn (bool $toParent) => ! $parent ? 'comment' : ($toParent ? 'reply_to_you' : 'reply');

    foreach ($users as $user) {
      Notify::send($user, new CommentNotification($base + ['mail_kind' => $kind((int) $user->id === $parentAuthorId)]));
    }
    if ($toMailbox && $mailbox) {
      Notify::one(Notification::route('mail', $mailbox), new CommentNotification($base + ['mail_kind' => $kind($parentIsMailbox)]));
    }
  }

  /** "Max Mustermann" for the comment mails (username if no name is stored). */
  private function personName($user): string
  {
    if (! $user) {
      return '';
    }

    return trim(($user->vorname ?? '') . ' ' . ($user->name ?? '')) ?: (string) $user->username;
  }
}
