@if($umfrages->isNotEmpty())
  <div class="row">
    @foreach($umfrages->groupBy('umcategory.id') as $categoryId => $links)
      @php $category = $links->first()->umcategory; @endphp
      <div class="col">
        <div class="category-container">
          <div class="d-flex align-items-center">
            <h4>{{ $category->name }}</h4>
            @if(auth()->user()->hasRole('Korso_Admin') || auth()->user()->hasRole('Super_Admin'))
              <a href="#" class="edit-category-icon ml-2" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}">
                <i class="fa fa-pen fa-xs"></i>
              </a>
            @endif
          </div>
          <ul class="list-unstyled">
            @foreach($links as $link)
              <li class="d-flex align-items-center">
                <a href="{{ $link->url }}" target="_blank">{{ $link->title }}</a>
                @if(auth()->user()->hasRole('Korso_Admin') || auth()->user()->hasRole('Super_Admin'))
                  <a href="{{ route('umfrages.edit', $link->id) }}" class="edit-icon ml-2">
                    <i class="fa fa-pen fa-xs"></i>
                  </a>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endforeach
  </div>
@else
  <p>Keine Links für diese Kategorie vorhanden.</p>
@endif
