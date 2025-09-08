<?php

namespace App\Http\Controllers;

use App\User;
use App\Korso;
use App\Handwerk;
use Illuminate\Http\Request;
use Laravelista\Comments\Comment;
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
use Laravelista\Comments\CommentControllerInterface;



class CommentController extends Controller implements CommentControllerInterface
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
    Validator::make($request->all(), array_merge($guest_rules ?? [], [
      'commentable_type' => 'required|string',
      'commentable_id'   => 'required|string|min:1',
      'message'          => 'required|string',
    ]))->validate();

    // Load the commentable model
    $model = $request->commentable_type::findOrFail($request->commentable_id);

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

    // Prepare notification payload
    $notifications = [
      'title'        => 'neuer Kommentar',
      'comment'      => $comment->comment,
      'commenter_id' => $comment->commenter->username,
      'id'           => $comment->commentable_id,
      'type'         => class_basename($model), // 'Ticket', 'Handwerk', or 'Korso'
    ];

    //
    // ─── HANDWERK LOGIC ────────────────────────────────────────────────────────────
    //
    if ($model instanceof Handwerk) {
      if ($comment->commenter_id == $model->submitter) {
        // Submitter commented
        if (! $model->assignedTo) {
          // No one assigned → send to fallback user 327
          $user327 = User::find(327);
          Notification::send($user327, new CommentNotification($notifications));
        } else {
          // Assigned → send only to assigned
          $assigned = User::find($model->assignedTo);
          Notification::send($assigned, new CommentNotification($notifications));
        }
      } else {
        // Someone else commented → notify submitter
        $submitter = User::find($model->submitter);
        Notification::send($submitter, new CommentNotification($notifications));
      }

      //
      // ─── KORSO LOGIC ──────────────────────────────────────────────────────────────
      //
    } elseif ($model instanceof Korso) {
      $submitter   = User::find($model->submitter);
      $assignedId  = $model->assignedTo;
      $commenterId = $comment->commenter_id;

      if (is_null($assignedId)) {
        // CASE 1: Unassigned → notify all Korso_ma except the commenter
        $korsoMAs = User::role('Korso_ma')
          ->where('id', '!=', $commenterId)
          ->get();

        Notification::send($korsoMAs, new CommentNotification($notifications));
      } else {

        if ($commenterId == $assignedId) {
          // CASE 2a: Assigned user commented → notify only the submitter (or SekGroup)
          if ($submitter && $submitter->id !== $commenterId) {
            if ($model->sek_group_id && $model->sekGroup) {
              // send to the group’s email instead of the user
              Notification::route('mail', $model->sekGroup->email)
                ->notify(new CommentNotification($notifications));
            } else {
              // fallback: the original user
              Notification::send($submitter, new CommentNotification($notifications));
            }
          }
        } else {
          // CASE 2b: Someone else commented → notify only the assigned user
          $assignedUser = User::find($assignedId);
          if ($assignedUser) {
            Notification::send($assignedUser, new CommentNotification($notifications));
          }
        }
      }

      //
      // ─── TICKET LOGIC ─────────────────────────────────────────────────────────────
      //
    } else {
      // Default logic for Tickets
      if ($comment->commenter_id == $model->submitter) {
        // Submitter commented
        if (! $model->assignedTo) {
          // No one assigned → notify all Super_Admins
          $admins = User::role('Super_Admin')->get();
          Notification::send($admins, new CommentNotification($notifications));
        } else {
          // Assigned → notify only assigned
          $assigned = User::find($model->assignedTo);
          Notification::send($assigned, new CommentNotification($notifications));
        }
      } else {
        // Someone else commented → notify submitter
        $submitter = User::find($model->submitter);
        Notification::send($submitter, new CommentNotification($notifications));
      }
    }

    // Save and redirect back to the new comment anchor
    $comment->save();

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

    // Common payload
    $notifications = [
      'title'        => 'Kommentarantwort',
      'id'           => $reply->commentable_id,
      'comment'      => $reply->comment,
      'commenter_id' => $reply->commenter->username,
    ];

    // The model we're commenting on
    $model = $comment->commentable;

    // ─── If it's a Korso, honor sek_group_id ────────────────────────
    if ($model instanceof Korso) {
      // Who would normally get this reply?
      $parentCommenterId = $reply->parent->commenter_id;

      // If parent commenter *is* the original Korso submitter…
      // but they submitted “as Sekretariat”, send to the group email instead
      if (
        $parentCommenterId == $model->submitter
        && $model->sek_group_id
        && $model->sekGroup
      ) {
        Notification::route('mail', $model->sekGroup->email)
          ->notify(new CommentNotification($notifications));
      } else {
        // Fallback: send to that single user
        $reply_to = User::find($parentCommenterId);
        if ($reply_to) {
          Notification::send($reply_to, new CommentNotification($notifications));
        }
      }

      // ─── Otherwise, do the original single-user reply routing ───────
    } else {
      $reply_to = User::where('id', $reply->parent->commenter_id)->first();
      if ($reply_to) {
        Notification::send($reply_to, new CommentNotification($notifications));
      }
    }

    $reply->save();

    return Redirect::to(URL::previous() . '#comment-' . $reply->getKey());
  }
}
