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

<div class="form-group form-check">
  <input
    type="checkbox"
    class="form-check-input"
    id="location_needed"
    name="location_needed"
    {{ old('location_needed', $item->location_needed ?? false) ? 'checked' : '' }}
  >
  <label class="form-check-label" for="location_needed">Standort benötigt</label>
</div>

<div class="form-group form-check">
  <input
    type="checkbox"
    class="form-check-input"
    id="massnahme_needed"
    name="massnahme_needed"
    {{ old('massnahme_needed', $item->massnahme_needed ?? false) ? 'checked' : '' }}
  >
  <label class="form-check-label" for="massnahme_needed">Maßnahme benötigt</label>
</div>

<button type="submit" class="btn btn-success">
  <i class="fas fa-save"></i> {{ $buttonText }}
</button>

<a href="{{ route('zertifizierung_items.index') }}" class="btn btn-secondary ml-2">Zurück</a>
