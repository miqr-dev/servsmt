<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between col-md-12">
      <div>
        <button class="btn {{ $buttonClass ?? 'btn-outline-primary' }}" onclick="window.history.back(1);">
          <i class="fas fa-long-arrow-alt-left fa-lg"></i>
        </button>
      </div>
      <div class="mr-5">
        <h1 class="ticket_header {{ $colorClass ?? '' }}">
          {{ $title }}
        </h1>
      </div>
      <div>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
