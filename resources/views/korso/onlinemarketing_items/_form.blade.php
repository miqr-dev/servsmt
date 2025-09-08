<div class="form-group">
  <label for="name">Bezeichnung</label>
  <input
    type="text"
    class="form-control @error('name') is-invalid @enderror"
    id="name"
    name="name"
    value="{{ old('name', $item->name ?? '') }}"
    required
  >
  @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<button type="submit" class="btn btn-success">
  <i class="fas fa-save"></i> {{ $buttonText }}
</button>

<a href="{{ route('onlinemarketing_items.index') }}" class="btn btn-secondary ml-2">Zurück</a>
