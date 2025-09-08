<div class="card-body col-lg-12 mt-0">
  @if($errors->has('commentable_type'))
  <div class="alert alert-danger" role="alert">
    {{ $errors->first('commentable_type') }}
  </div>
  @endif
  @if($errors->has('commentable_id'))
  <div class="alert alert-danger" role="alert">
    {{ $errors->first('commentable_id') }}
  </div>
  @endif
  <form method="POST" action="{{ route('comments.store') }}">
    @csrf
    @honeypot
    <input type="hidden" name="commentable_type" value="\{{ get_class($model) }}" />
    <input type="hidden" name="commentable_id" value="{{ $model->getKey() }}" />

    {{-- Guest commenting --}}
    @if(isset($guest_commenting) and $guest_commenting == true)
    <div class="form-group">
      <label for="message">Enter your name here:</label>
      <input type="text" class="form-control @if($errors->has('guest_name')) is-invalid @endif" name="guest_name" />
      @error('guest_name')
      <div class="invalid-feedback">
        {{ $message }}
      </div>
      @enderror
    </div>
    <div class="form-group">
      <label for="message">Enter your email here:</label>
      <input type="email" class="form-control @if($errors->has('guest_email')) is-invalid @endif" name="guest_email" />
      @error('guest_email')
      <div class="invalid-feedback">
        {{ $message }}
      </div>
      @enderror
    </div>
    @endif
    @if(!$isDone)
    <div class="form-group">
      <label for="message">Tragen Sie Ihre Nachricht hier ein:</label>
      <textarea class="form-control @if($errors->has('message')) is-invalid @endif notizen" name="message" rows="3"
        height="100px" style="resize:none;"></textarea>
    </div>
    <button type="submit" class="btn btn-sm btn-outline-success">Einreichen</button>
    @else
    <div>
      <div class="col-lg-12">
        <h4 style="color:#661421; font-weight:bold;">Das Ticket ist erledigt.</h4>
        <p style="color:#661421; font-weight:bold;">Zum hinzufügen von weiteren Kommentaren klicken Sie bitte
          "Wiederherstellen".</p>
        <img src="../../images/admin_images/communi.png" style="max-width:50%;" alt="Kein Kommentar">
      </div>
    </div>
    @endif
  </form>
</div>
<br />