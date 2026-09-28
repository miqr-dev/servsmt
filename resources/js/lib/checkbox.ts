// Checkbox columns on `tickets` / `handwerks` are plain strings. The old
// Blade forms stored "on" (ticked) or NULL; Vue forms before the 2026-09-28
// store() fix stored "1" / "0" - and "0" is truthy in JS, so the detail
// pages showed every option as selected. Treat every "not ticked" spelling
// as false so both old and already-saved rows display correctly.
export function isChecked(v: unknown): boolean {
    if (v === null || v === undefined || v === false || v === 0) return false;
    const s = String(v).trim().toLowerCase();
    return s !== '' && s !== '0' && s !== 'false' && s !== 'off';
}
