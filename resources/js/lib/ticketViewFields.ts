import type { CommentItem } from '@/components/CommentThread.vue';

/**
 * Converted from resources/views/tickets/admins/showticket.blade.php and the
 * 35 files under resources/views/tickets/admins/view_ticket_blades/*.blade.php
 * (one per Ticket.problem_type, @include'd dynamically by the old page via
 * `$blade_name = 'tickets.admins.view_ticket_blades.' .
 * str_replace(' ', '', strtolower($ticket->problem_type)) . 'ticket'`).
 *
 * 33 of the 35 partials are the exact same shape Handwerk's 14 type-specific
 * partials were (see handwerkItems.ts) - a fixed list of "label -> value"
 * rows, plus for some types a second group of boolean-flag rows shown as a
 * green-check badge when true, under a "Probleme" (or similar) heading.
 * Collapsed here into one data table instead of 33 separate Vue files, same
 * reasoning as Handwerk's ITEM_GROUPS_BY_TYPE. The remaining 2
 * (neuermitarbeiterticket, neuerteilnehmerticket) don't fit this shape at all
 * - one has editable autosaving inputs and a long list of one-off
 * conditional sections, the other is an editable participant table - both
 * are hand-built directly in Tickets/Show.vue instead of forced in here.
 *
 * TicketController@show computes `viewKey` server-side with the exact same
 * formula the old `$blade_name` used (just without the Blade path prefix),
 * so this catalog's keys are exactly the old partials' filenames minus
 * ".blade.php" - e.g. "druckerfehlerticket". Doing the derivation
 * server-side (rather than re-implementing str_replace/strtolower in JS)
 * avoids any risk of a subtly different case-folding result between PHP and
 * JS ever silently pointing a ticket at the wrong entry here.
 *
 * A field's `value()` takes the whole page context, not just the ticket,
 * because 3 of the types (andererdruckerstandort/andererpc/anderertelefon-
 * standortticket) render a "Neue Adresse"/"Neuer Raum" pair that comes from
 * $telNewAddress/$telNewRoom - two *separate* models the old controller
 * looks up unconditionally on every show() call (by tel_target_place/
 * tel_target_room), not from the ticket's own location/room relation. Easy
 * to conflate since hardwareanfrageticket's "Adresse"/"Raum" *does* read the
 * ticket's own location/room - kept the distinction exactly as the old
 * partials had it.
 *
 * Bug found and fixed while transcribing scannereinrichtenticket.blade.php /
 * scannerproblemeticket.blade.php (byte-identical old files): both had a
 * copy-paste error in their printer "Raum" row, reading
 * `$ticket->invitem->invroom->altrname` for the second half of the string
 * instead of `$ticket->printer->invroom->altrname` - so the printer's room
 * always showed the *computer's* alt-room-name next to the printer's own
 * room name. Fixed below (see the two `printer?.invroom?.altrname` rows).
 */

type NamedUser = { vorname: string | null; name: string | null; username: string | null; ort: string | null; straße: string | null } | null;
type Place = { pnname: string | null } | null;
type LocationInfo = { address: string | null; place: Place } | null;
type RoomInfo = { rname: string | null; altrname: string | null; location: LocationInfo } | null;
type InvItemInfo = { gname: string | null; invnr: string | null; invroom: RoomInfo } | null;
type NamedItem = { name: string | null } | null;
type ReplicationUser = { name: string | null; vorname: string | null } | null;
type ForwardUser = { name: string | null; vorname: string | null } | null;

export type Participant = {
    id: number;
    vorname: string | null;
    nachname: string | null;
    username: string | null;
    password: string | null;
    course: string | null;
    email: string | null;
    location: string | null;
    notes_participant: string | null;
};

export interface TicketDetail {
    id: number;
    problem_type: string;
    notizen: string | null;
    tel_number: string | null;
    custom_tel_number: string | null;
    priority_id: number;
    ticket_status_id: number | null;
    assignedTo: number | null;
    on_location: string | null;
    deleted_at: string | null;
    done_by: string | null;
    created_at: string;
    subUser: NamedUser;
    user: { username: string | null } | null;
    invitem: InvItemInfo;
    printer: InvItemInfo;
    pcs: { gname: string | null; invnr: string | null }[];
    location: LocationInfo;
    room: { rname: string | null; altrname: string | null } | null;
    gart: NamedItem;
    replication: ReplicationUser;
    forwardOnUser: ForwardUser;
    forwardFromUser: ForwardUser;
    ticket_status: NamedItem;
    ticket_priority: NamedItem;
    comments: CommentItem[];

    // Per-type plain columns - only the ones actually read by a
    // view_ticket_blades partial or showticket.blade.php itself.
    searchsoftware: string | null;
    software_name: string | null;
    software_reason: string | null;
    password_name: string | null;
    abgelaufen: boolean;
    expiring_date: string | null;
    inaktiv: boolean;
    forgotten: boolean;
    bbb_subject: string | null;
    bbb_username: string | null;
    smt_subject: string | null;
    smt_username: string | null;
    firmen_subject: string | null;
    firmen_username: string | null;
    vtiger_subject: string | null;
    vtiger_username: string | null;
    user_other_username: string | null;
    forward_required_at: string | null;
    forward_to_at: string | null;
    cancelForward: boolean;
    current_tel_name: string | null;
    new_tel_name: string | null;
    new_tel_number: string | null;
    terminal_name: string | null;
    terminal_expiry: string | null;
    terminal_datev: boolean;
    terminal_lexware: boolean;
    user_oldname: string | null;
    user_newname: string | null;
    keyboard: boolean;
    mouse: boolean;
    speaker: boolean;
    headset: boolean;
    webcam: boolean;
    monitor: boolean;
    other: boolean;
    geht_nicht_an: boolean;
    blue: boolean;
    black: boolean;
    slow_computer: boolean;
    web_cam_problem: boolean;
    head_set_problem: boolean;
    lautsprecher_mal: boolean;
    keyboard_malfunction: boolean;
    mouse_mal: boolean;
    slow_network: boolean;
    no_network_drive: boolean;
    laud_fan: boolean;
    scanner_wrong_folder: boolean;
    scanner_not_working: boolean;
    scanner_myname_list: boolean;
    pc_laptop_others: string | null;

    // neuermitarbeiterticket - handled directly in Show.vue, listed here
    // only so TicketDetail is the single complete prop type.
    employee_username: string | null;
    employee_email: string | null;
    employee_finish_at: string | null;
    employee_required_at: string | null;
    employee_lastname: string | null;
    employee_firstname: string | null;
    telephone_employee: string | null;
    position_employee: string | null;
    abteilung_employee: string | null;
    isplus: boolean;
    outlook: boolean;
    email_berlin: boolean;
    email_erfurt: boolean;
    email_suhl: boolean;
    email_leipzig: boolean;
    email_dresden: boolean;
    email_chemnitz: boolean;
    email_lasch: boolean;
    email_lorenz: boolean;
    email_custom: string | null;
}

/** Everything a view-group's value() function might need - see the "Neue Adresse" doc comment above. */
export interface TicketViewContext {
    ticket: TicketDetail;
    telNewRoom: { rname: string | null; altrname: string | null } | null;
    telNewAddress: { address: string | null } | null;
}

export interface TicketViewField {
    label: string;
    value: (ctx: TicketViewContext) => string;
}
export interface TicketViewFlag {
    label: string;
    key: keyof TicketDetail;
}
export interface TicketViewGroup {
    /** Defaults to ticket.problem_type when omitted - only pclaptopsonstigesticket overrides this. */
    heading?: (ctx: TicketViewContext) => string;
    fields: TicketViewField[];
    /** Heading shown above `flags`, only when at least one of them is true. */
    flagsHeading?: string;
    flags?: TicketViewFlag[];
    /** A single conditionally-shown highlighted warning line (emailweiterleitungticket's "Weiterleitung aufheben"). */
    warning?: { key: keyof TicketDetail; label: string };
}

const room = (r: { rname: string | null; altrname: string | null } | null | undefined) => [r?.rname, r?.altrname].filter(Boolean).join(' | ');

const rechnerAdresseRaum: TicketViewField[] = [
    { label: 'Rechner', value: ({ ticket: t }) => t.invitem?.gname ?? '' },
    { label: 'Adresse', value: ({ ticket: t }) => t.invitem?.invroom?.location?.address ?? '' },
    { label: 'Raum', value: ({ ticket: t }) => room(t.invitem?.invroom) },
];
const telefonAdresseRaum: TicketViewField[] = [
    { label: 'Telefon', value: ({ ticket: t }) => t.invitem?.gname ?? '' },
    { label: 'Adresse', value: ({ ticket: t }) => t.invitem?.invroom?.location?.address ?? '' },
    { label: 'Raum', value: ({ ticket: t }) => room(t.invitem?.invroom) },
];
const druckerAdresseRaum: TicketViewField[] = [
    { label: 'Drucker', value: ({ ticket: t }) => t.printer?.gname ?? '' },
    { label: 'Adresse', value: ({ ticket: t }) => t.printer?.invroom?.location?.address ?? '' },
    { label: 'Raum', value: ({ ticket: t }) => room(t.printer?.invroom) },
];
const neueAdresseNeuerRaum: TicketViewField[] = [
    { label: 'Neue Adresse', value: ({ telNewAddress }) => telNewAddress?.address ?? '' },
    { label: 'Neuer Raum', value: ({ telNewRoom }) => room(telNewRoom) },
];
const betreffName = (subjectKey: keyof TicketDetail, usernameKey: keyof TicketDetail): TicketViewField[] => [
    { label: 'Betreff', value: ({ ticket: t }) => (t[subjectKey] as string | null) ?? '' },
    { label: 'Name', value: ({ ticket: t }) => (t[usernameKey] as string | null) ?? '' },
];
const betreffOnly = (subjectKey: keyof TicketDetail): TicketViewField[] => [
    { label: 'Betreff', value: ({ ticket: t }) => (t[subjectKey] as string | null) ?? '' },
];
const formattedDate = (value: string | null): string => (value ? new Date(value).toLocaleDateString('de-DE') : '');

export const TICKET_VIEW_GROUPS: Record<string, TicketViewGroup> = {
    aktivierenticket: {
        fields: [...rechnerAdresseRaum, { label: 'Software', value: ({ ticket: t }) => t.searchsoftware ?? '' }],
    },
    andererdruckerstandortticket: {
        fields: [{ label: 'Drucker', value: ({ ticket: t }) => t.invitem?.gname ?? '' }, ...neueAdresseNeuerRaum],
    },
    andererpcstandortticket: {
        fields: [
            {
                label: 'PCs',
                value: ({ ticket: t }) =>
                    t.pcs.length ? t.pcs.map((pc) => pc.gname || pc.invnr).join(', ') : ((t.invitem?.gname || t.invitem?.invnr) ?? '—'),
            },
            ...neueAdresseNeuerRaum,
        ],
    },
    anderertelefonstandortticket: {
        fields: [...telefonAdresseRaum, ...neueAdresseNeuerRaum],
    },
    anmeldeproblemeticket: {
        fields: [{ label: 'Name', value: ({ ticket: t }) => t.password_name ?? '' }, ...rechnerAdresseRaum],
        flagsHeading: 'Probleme',
        flags: [
            { label: 'Freie Mitarbeiter Konto Abgelaufen', key: 'abgelaufen' },
            { label: 'Inaktiv', key: 'inaktiv' },
            { label: 'Kennenwort Vorgessen / Konto gesperrt', key: 'forgotten' },
        ],
    },
    bbbfehlermeldungticket: { fields: betreffName('bbb_subject', 'bbb_username') },
    bbbfunktionsanfragenticket: { fields: betreffOnly('bbb_subject') },
    bbbneuerbenutzerticket: { fields: betreffName('bbb_subject', 'bbb_username') },
    beamerproblemeticket: {
        fields: [
            { label: 'Beamer', value: ({ ticket: t }) => t.invitem?.gname ?? '' },
            { label: 'Adresse', value: ({ ticket: t }) => t.invitem?.invroom?.location?.address ?? '' },
            { label: 'Raum', value: ({ ticket: t }) => room(t.invitem?.invroom) },
        ],
    },
    benutzersonstigesticket: { fields: [{ label: 'Name', value: ({ ticket: t }) => t.user_other_username ?? '' }] },
    druckereinrichtenticket: { fields: [...rechnerAdresseRaum, ...druckerAdresseRaum] },
    druckerfehlerticket: { fields: [...rechnerAdresseRaum, ...druckerAdresseRaum] },
    funktionsanfrageticket: { fields: [...rechnerAdresseRaum, ...druckerAdresseRaum] },
    emailweiterleitungticket: {
        fields: [
            {
                label: 'E-Mails an folgenden Empfänger weiterleiten',
                value: ({ ticket: t }) => `${t.forwardOnUser?.name ?? ''}, ${t.forwardOnUser?.vorname ?? ''}`,
            },
            { label: 'Von', value: ({ ticket: t }) => `${t.forwardFromUser?.name ?? ''}, ${t.forwardFromUser?.vorname ?? ''}` },
            { label: 'Ab', value: ({ ticket: t }) => formattedDate(t.forward_required_at) },
            { label: 'Bis', value: ({ ticket: t }) => (t.forward_to_at ? formattedDate(t.forward_to_at) : '-') },
        ],
        warning: { key: 'cancelForward', label: 'Weiterleitung aufheben' },
    },
    firmenvzfehlermeldungticket: { fields: betreffName('firmen_subject', 'firmen_username') },
    firmenvzfunktionsanfragenticket: { fields: betreffOnly('firmen_subject') },
    hardwareanfrageticket: {
        fields: [
            { label: 'Geräte', value: ({ ticket: t }) => t.gart?.name ?? '' },
            { label: 'Adresse', value: ({ ticket: t }) => t.location?.address ?? '' },
            { label: 'Raum', value: ({ ticket: t }) => room(t.room) },
        ],
    },
    pclaptopsonstigesticket: { heading: ({ ticket: t }) => t.pc_laptop_others ?? '', fields: [] },
    peripherieanfrageticket: {
        fields: [{ label: 'Rechner', value: ({ ticket: t }) => t.invitem?.gname ?? '' }],
        flags: [
            { label: 'Tastatur', key: 'keyboard' },
            { label: 'Maus', key: 'mouse' },
            { label: 'Lautsprecher', key: 'speaker' },
            { label: 'Kopfhörer', key: 'headset' },
            { label: 'Webcam', key: 'webcam' },
            { label: 'Bildschirm', key: 'monitor' },
            { label: 'Sonstiges', key: 'other' },
        ],
    },
    problemeticket: {
        fields: rechnerAdresseRaum,
        flagsHeading: 'Probleme',
        flags: [
            { label: 'Geht nicht an', key: 'geht_nicht_an' },
            { label: 'Geht an / Blue Screen', key: 'blue' },
            { label: 'Geht an / Black Screen', key: 'black' },
            { label: 'Sehr Langsam', key: 'slow_computer' },
            { label: 'Webcam funktioniert nicht', key: 'web_cam_problem' },
            { label: 'Headset funktioniert nicht', key: 'head_set_problem' },
            { label: 'Lautsprecher funktioniert nicht', key: 'lautsprecher_mal' },
            { label: 'Tastatur funktioniert nicht', key: 'keyboard_malfunction' },
            { label: 'Maus funktioniert nicht', key: 'mouse_mal' },
            { label: 'Netzwerkzugriff langsam', key: 'slow_network' },
            { label: 'Keine Netzlaufwerke', key: 'no_network_drive' },
            { label: 'lautes Lüftergeräusch', key: 'laud_fan' },
            { label: 'Sonstiges', key: 'other' },
        ],
    },
    // scannereinrichtenticket / scannerproblemeticket: byte-identical old
    // partials, same copy-paste "Raum" bug fixed here - see file doc comment.
    scannereinrichtenticket: {
        fields: [...rechnerAdresseRaum, ...druckerAdresseRaum],
        flagsHeading: 'Probleme',
        flags: [
            { label: 'Scans nicht im Scan Ordner', key: 'scanner_wrong_folder' },
            { label: 'Scan funktioniert nicht', key: 'scanner_not_working' },
            { label: 'Scanner einrichten', key: 'scanner_myname_list' },
        ],
    },
    scannerproblemeticket: {
        fields: [...rechnerAdresseRaum, ...druckerAdresseRaum],
        flagsHeading: 'Probleme',
        flags: [
            { label: 'Scans nicht im Scan Ordner', key: 'scanner_wrong_folder' },
            { label: 'Scan funktioniert nicht', key: 'scanner_not_working' },
            { label: 'Scanner einrichten', key: 'scanner_myname_list' },
        ],
    },
    smtfehlermeldungticket: { fields: betreffName('smt_subject', 'smt_username') },
    smtfunktionsanfragenticket: { fields: betreffOnly('smt_subject') },
    softwareanfragesonstigesticket: { fields: rechnerAdresseRaum },
    softwareinstallierenticket: {
        fields: [
            ...rechnerAdresseRaum,
            { label: 'Software', value: ({ ticket: t }) => t.searchsoftware ?? '' },
            { label: 'Software-Link', value: ({ ticket: t }) => t.software_name ?? '' },
            { label: 'Grund', value: ({ ticket: t }) => t.software_reason ?? '' },
        ],
    },
    telefonnamenswechselticket: {
        fields: [
            ...telefonAdresseRaum,
            { label: 'Alter Name', value: ({ ticket: t }) => t.current_tel_name ?? '' },
            { label: 'Neuer Name', value: ({ ticket: t }) => t.new_tel_name ?? '' },
        ],
    },
    telefonnummerntauschticket: {
        fields: [...telefonAdresseRaum, { label: 'Neue Nummer', value: ({ ticket: t }) => t.new_tel_number ?? '' }],
    },
    telproblemeticket: { fields: telefonAdresseRaum },
    terminaltnbenutzerticket: {
        fields: [
            { label: 'Benutzername', value: ({ ticket: t }) => t.terminal_name ?? '' },
            { label: 'Maßnahmeende', value: ({ ticket: t }) => t.terminal_expiry ?? '' },
        ],
        flags: [
            { label: 'Datev', key: 'terminal_datev' },
            { label: 'Lexware', key: 'terminal_lexware' },
        ],
    },
    vtigerfehlermeldungticket: { fields: betreffName('vtiger_subject', 'vtiger_username') },
    vtigerfunktionsanfragenticket: { fields: betreffOnly('vtiger_subject') },
    wechselnameticket: {
        fields: [
            { label: 'Alter Name', value: ({ ticket: t }) => t.user_oldname ?? '' },
            { label: 'Neuer Name', value: ({ ticket: t }) => t.user_newname ?? '' },
        ],
    },
};
