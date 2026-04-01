@php
if (isset($approved) and $approved == true) {
$comments = $model->approvedComments;
} else {
$comments = $model->comments;
}
$isTicket = isset($ticket);
$isKorso = isset($korso);
$isDone = ($isTicket && $ticket->deleted_at) || ($isKorso && $korso->deleted_at);
@endphp

<style>
  .comment-content img {
    cursor: zoom-in;
  }

  #comment-image-preview-modal .modal-dialog {
    max-width: none;
    width: auto;
    margin: 1rem;
  }

  #comment-image-preview-modal .modal-body {
    overflow: auto;
    max-height: calc(100vh - 2rem);
  }

  #comment-image-preview {
    max-width: none;
    width: auto;
    height: auto;
  }
</style>


<div class="row">
  @if($comments->count() < 1 && is_null(@$ticket->deleted_at))
    <div class="col-lg-4">
      <img src="../../images/admin_images/comment_400.png" style="max-width:50%;" alt="Kein Kommentar">
    </div>
    @if(auth()->user()->hasRole('Super_Admin') && isset($ticket))
    <div class="col-lg-8">
      <div class="form-group col-md-12">
        <label for="admin_notes" style="color:#be123c"> Interne Notizen </label>
        <textarea class="form-control admin_notes" id="admin_notes" name="admin_notes"
          style="resize:none;">{{ @$ticket->admin_notes ? $ticket->admin_notes : ''  }}</textarea>
      </div>
    </div>
    @endif
    @if(auth()->user()->hasAnyRole('Super_Admin','handwerk_admin') && isset($handwerk))
    <div class="col-lg-8">
      <div class="form-group col-md-12">
        <label for="admin_notes" style="color:#be123c"> Interne Notizen </label>
        <textarea class="form-control admin_notes" id="admin_notes_handwerk" name="admin_notes"
          style="resize:none;">{{ @$handwerk->admin_notes ? $handwerk->admin_notes : ''  }}</textarea>
      </div>
    </div>
    @endif
</div>
@else
<div class="row col-md-12">
  <div class="col-lg-6">
    <ul class="list-unstyled">
      @php
      $comments = $comments->sortBy('created_at');

      if (isset($perPage)) {
      $page = request()->query('page', 1) - 1;

      $parentComments = $comments->where('child_id', '');

      $slicedParentComments = $parentComments->slice($page * $perPage, $perPage);

      $m = Config::get('comments.model'); // This has to be done like this, otherwise it will complain.
      $modelKeyName = (new $m)->getKeyName(); // This defaults to 'id' if not changed.

      $slicedParentCommentsIds = $slicedParentComments->pluck($modelKeyName)->toArray();

      // Remove parent Comments from comments.
      $comments = $comments->where('child_id', '!=', '');

      $grouped_comments = new \Illuminate\Pagination\LengthAwarePaginator(
      $slicedParentComments->merge($comments)->groupBy('child_id'),
      $parentComments->count(),
      $perPage
      );

      $grouped_comments->withPath(request()->path());
      } else {
      $grouped_comments = $comments->groupBy('child_id');
      }
      @endphp
      @foreach($grouped_comments as $comment_id => $comments)
      {{-- Process parent nodes --}}
      @if($comment_id == '')
      @foreach($comments as $comment)
      @include('comments::_comment', [
      'comment' => $comment,
      'grouped_comments' => $grouped_comments
      ])
      @endforeach
      @endif
      @endforeach
    </ul>

    @isset ($perPage)
    {{ $grouped_comments->links() }}
    @endisset
    <!-- <div class="card">
          <div class="card-body">
              <h5 class="card-title">Authentication required</h5>
              <p class="card-text">You must log in to post a comment.</p>
              <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
          </div>
      </div> -->

  </div>
  @if(auth()->user()->hasRole('Super_Admin') && isset($ticket) )
  <div class="col-lg-6">
    <div class="form-group col-md-12">
      <label for="admin_notes" style="color:#be123c"> Interne Notizen </label>
      <textarea class="form-control admin_notes" id="admin_notes" name="admin_notes"
        style="resize:none;">{{ @$ticket->admin_notes ? $ticket->admin_notes : ''  }}</textarea>
    </div>
  </div>
  @endif
</div>
@endif
<div class="row col-md-12">
  <div class="col-lg-12">
    @auth
    @include('comments::_form')
    @elseif(Config::get('comments.guest_commenting') == true)
    @include('comments::_form', [
    'guest_commenting' => true
    ])
    @else
    @endauth
  </div>
</div>

<div class="modal fade" id="comment-image-preview-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body text-center p-2">
        <img id="comment-image-preview" src="" alt="Kommentarbild Vorschau">
      </div>
    </div>
  </div>
</div>

<script>
  if (!window.commentImagePreviewInitialized) {
    window.commentImagePreviewInitialized = true;

    document.addEventListener('click', function (event) {
      var target = event.target;

      if (!(target instanceof HTMLImageElement)) {
        return;
      }

      if (!target.closest('.media-body') || target.closest('#comment-image-preview-modal')) {
        return;
      }

      var modalImage = document.getElementById('comment-image-preview');
      if (!modalImage) {
        return;
      }

      modalImage.src = target.currentSrc || target.src;
      $('#comment-image-preview-modal').modal('show');
    });

    $('#comment-image-preview-modal').on('hidden.bs.modal', function () {
      var modalImage = document.getElementById('comment-image-preview');
      if (modalImage) {
        modalImage.src = '';
      }
    });
  }
</script>
