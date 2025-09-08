@inject('markdown', 'Parsedown')
@php($markdown->setSafeMode(true))

@if(isset($reply) && $reply === true)
  <div id="comment-{{ $comment->getKey() }}" class="media">
@else
  <li id="comment-{{ $comment->getKey() }}" class="media">
@endif
    <img class="mr-3 rounded-circle" src="/images/admin_images/mitarbeiter/{{($comment->commenter->name)}}, {{($comment->commenter->vorname)}}.jpg" alt="{{ $comment->commenter->name ?? $comment->guest_name }} Avatar"
    onerror="this.onerror=null;this.src='/images/admin_images/mitarbeiter/nopic.jpg';">
    <div class="media-body">
        <h5 class="mt-0 mb-1" style="font-weight:bold; color: #661421;">{{ $comment->commenter->username ?? $comment->guest_name }} <small class="text-muted">- {{ $comment->created_at->diffForHumans() }}</small></h5>
        <div style="white-space: pre-wrap; font-weight:bold;">{!! $comment->comment !!}</div>

        @if(!$isDone)
        <div>
            @can('reply-to-comment', $comment)
                <span data-toggle="modal" data-target="#reply-modal-{{ $comment->getKey() }}"><button class="btn btn-sm btn-outline-primary text-uppercase">Antworten &nbsp;<i class="fas fa-reply fa-lg"></i></button></span>
            @endcan
            @can('edit-comment', $comment)
                <span data-toggle="modal" data-target="#comment-modal-{{ $comment->getKey() }}"><button data-toggle="tooltip" data-placement="left" title="Bearbeiten" class="btn btn-sm text-uppercase text-primary"><i class="fas fa-edit fa-lg"></i></button></span>
            @endcan
            <!-- @can('delete-comment', $comment)
                <a href="{{ route('comments.destroy', $comment->getKey()) }}" onclick="event.preventDefault();document.getElementById('comment-delete-form-{{ $comment->getKey() }}').submit();" class="btn btn-sm btn-link text-danger text-uppercase" data-toggle="tooltip" data-placement="right" title="Löschen"><i class="far fa-trash-alt fa-lg"></i></a>
                <form id="comment-delete-form-{{ $comment->getKey() }}" action="{{ route('comments.destroy', $comment->getKey()) }}" method="POST" style="display: none;">
                    @method('DELETE')
                    @csrf
                </form>
            @endcan -->
        </div>
        @endif

        @can('edit-comment', $comment)
            <div class="modal fade" id="comment-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('comments.update', $comment->getKey()) }}">
                            @method('PUT')
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Kommentar bearbeiten</h5>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="message">Aktualisieren Sie Ihren Kommentar</label>
                                    <textarea required class="form-control" name="message" rows="5" style="resize: none;">{{ $comment->comment }}</textarea>
                                    <small class="form-text text-muted">.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-sm btn-outline-success text-uppercase">Ändern</button>
                              <button type="button" class="btn btn-sm btn-outline-danger text-uppercase" data-dismiss="modal">Verwerfen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('reply-to-comment', $comment)
            <div class="modal fade" id="reply-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('comments.reply', $comment->getKey()) }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Auf Kommentar antworten</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="message">Ihr Kommentar</label>
                                    <textarea required class="form-control" name="message" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-sm btn-outline-success text-uppercase">Antwort</button>
                                <button type="button" class="btn btn-sm btn-outline-danger text-uppercase" data-dismiss="modal">Verwerfen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        <br />{{-- Margin bottom --}}

        {{-- Recursion for children --}}
        @if($grouped_comments->has($comment->getKey()))
            @foreach($grouped_comments[$comment->getKey()] as $child)
                @include('comments::_comment', [
                    'comment' => $child,
                    'reply' => true,
                    'grouped_comments' => $grouped_comments
                ])
            @endforeach
        @endif

    </div>
@if(isset($reply) && $reply === true)
  </div>
@else
  </li>
@endif