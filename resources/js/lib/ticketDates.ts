// Date helpers for the Tickets creation forms. The old Blade forms used
// jQuery daterangepicker with `format: 'DD-MM-YYYY'`, and TicketController
// still expects exactly that on the wire (Email Weiterleitung validates
// `date_format:d-m-Y`; employee_required_at / employee_finish_at /
// participant_required_at are assigned raw). The Vue pages use a native
// <input type="date"> (ISO YYYY-MM-DD) instead and convert at submit time
// via form.transform(), so nothing server-side changes.

function pad(n: number): string {
    return String(n).padStart(2, '0');
}

/** Local-time ISO date (YYYY-MM-DD), optionally offset by `days`. */
export function isoDate(days = 0): string {
    const d = new Date();
    d.setDate(d.getDate() + days);
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

/** "2026-10-05" -> "05-10-2026"; empty/invalid input -> "". */
export function isoToDmy(iso: string | null | undefined): string {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso ?? '');
    return m ? `${m[3]}-${m[2]}-${m[1]}` : '';
}
