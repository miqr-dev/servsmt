<?php

namespace App\Http\Controllers;

use App\Ticket;
use Illuminate\Http\Request;
use App\ParticipantTicketTable;
use Illuminate\Support\Facades\Redirect;
use App\Exports\ParticipantListExport;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Illuminate\Support\Facades\Schema;

class ParticipantTicketTableController extends Controller
{

  // Access (2026-09-28, your call): Super_Admin sees every Teilnehmer,
  // Teilnehmer_Info sees only its own Standort. Verwaltung used to be allowed
  // too, but Verwaltung is the default role every new LDAP user gets on first
  // login - i.e. practically every employee could read participant
  // passwords for their Standort. Removed on purpose.
  const ACCESS_ROLES = 'role:Super_Admin|Teilnehmer_Info';
  const PER_PAGE = 50;

  function __construct()
  {
    // index2 (the old /participants/test debug route) was unprotected - any
    // logged-in user could read every Teilnehmer incl. passwords. Now gated too.
    $this->middleware(self::ACCESS_ROLES)->only(['index', 'index2', 'exportRows', 'export']);
  }

  /**
   * Teilnehmer Liste (Participants/Index.vue).
   *
   * Server-side paginated, 50 per page, no date limit (the old page silently
   * only ever showed the last 6 months). Search runs in the database across
   * ALL of the user's visible Teilnehmer, not just the loaded page.
   */
  public function index(Request $request)
  {
    $user = Auth()->user();
    $isSuperAdmin = $user->hasRole('Super_Admin');

    $paginator = $this->filteredQuery($request)
      ->paginate(self::PER_PAGE)
      ->withQueryString();

    return Inertia::render('Participants/Index', [
      'participants' => collect($paginator->items())->map(fn ($p) => $this->rowPayload($p))->values(),
      'pagination' => [
        'page' => $paginator->currentPage(),
        'pageCount' => $paginator->lastPage(),
        'total' => $paginator->total(),
        'from' => $paginator->firstItem() ?? 0,
        'to' => $paginator->lastItem() ?? 0,
        'perPage' => self::PER_PAGE,
      ],
      'filters' => [
        'search' => trim((string) $request->input('search', '')),
        'location' => $isSuperAdmin ? (string) $request->input('location', '') : '',
      ],
      'isSuperAdmin' => $isSuperAdmin,
      // Standort filter options - Super_Admin only (everyone else is already
      // locked to their own Standort).
      'locations' => $isSuperAdmin
        ? ParticipantTicketTable::whereNotNull('location')->where('location', '!=', '')
          ->distinct()->orderBy('location')->pluck('location')->values()
        : [],
      'scopeLabel' => $isSuperAdmin ? null : $this->ownLocation($user),
    ]);
  }

  /**
   * Every row matching the current search/filter (same scoping as index,
   * no paging) as JSON - feeds the client-side Kopieren / CSV /
   * "Alle drucken" buttons, which in the old DataTables page also worked on
   * all filtered rows, not just the visible page.
   */
  public function exportRows(Request $request)
  {
    return response()->json(
      $this->filteredQuery($request)->get()->map(fn ($p) => $this->rowPayload($p))->values()
    );
  }

  /**
   * Server-generated downloads for the three buttons that need a real file
   * format: excel, excel-extended ("Excel Erweitert") and pdf. Column sets
   * and order are copied from the old DataTables button config.
   */
  public function export(Request $request, string $type)
  {
    abort_unless(in_array($type, ['excel', 'excel-extended', 'pdf'], true), 404);

    $rows = $this->filteredQuery($request)->get()->map(fn ($p) => $this->rowPayload($p))->values();
    $title = 'MIQR | SMT'; // the old page's <title>, which DataTables used as export title
    $stamp = now()->format('Y-m-d');

    if ($type === 'pdf') {
      $pdf = PDF::loadView('participants.list_pdf', ['rows' => $rows, 'title' => $title]);
      return $pdf->download("Teilnehmer_{$stamp}.pdf");
    }

    if ($type === 'excel') {
      $headings = ['Vorname', 'Nachname', 'Benutzername', 'Standort', 'Passwort', 'Alter', 'Maßnahme', 'Deaktivierungsdatum', 'Kurse', 'Gruppe', 'Branch'];
      $data = $rows->map(fn ($r) => [
        $r['vorname'], $r['nachname'], $r['username'], $r['location'], $r['password'], '',
        $r['course'], $r['deaktivierungsdatum'], $r['kurs'], $r['gruppe'], $r['branch'],
      ])->all();
      $file = "Teilnehmer_{$stamp}.xlsx";
    } else {
      $headings = ['Nr.', 'Vorname', 'Nachname', 'Maßnahme', 'Standort', 'Alter', 'Geb.datum', 'Dauer', 'Beginn', 'Ende', 'Berater', 'MA/Team', 'Bemerkungen', 'Benutzername', 'Passwort', 'A 1.TT', 'A 2.TT'];
      $data = $rows->values()->map(fn ($r, $i) => [
        $i + 1, $r['vorname'], $r['nachname'], $r['course'], $r['location'],
        '', '', '', '', '', '', '', '', $r['username'], $r['password'], '', '',
      ])->all();
      $file = "Teilnehmer_erweitert_{$stamp}.xlsx";
    }

    return Excel::download(new ParticipantListExport($title, $headings, $data), $file);
  }

  // --- helpers -------------------------------------------------------------

  /** Base query scoped to what the current user may see. */
  private function scopedQuery($user)
  {
    $query = ParticipantTicketTable::query()
      ->select('participant_ticket_tables.*')
      ->with(['ticket' => function ($q) {
        $q->select('id', 'created_at', 'done_by');
      }]);

    if (! $user->hasRole('Super_Admin')) {
      $query->where('location', $this->ownLocation($user));
    }

    return $query;
  }

  /**
   * The Standort a non-admin sees - same rule as the old index(): Berlin is
   * split by street address into Berlin-PP / Berlin-TBR, everyone else by
   * their `ort`.
   */
  private function ownLocation($user)
  {
    if ($user->ort === 'Berlin') {
      if ($user->straße === 'Prenzlauer Promenade 28') {
        return 'Berlin-PP';
      }
      if ($user->straße === 'Trachenbergring 93') {
        return 'Berlin-TBR';
      }
      return 'Berlin';
    }

    return $user->ort;
  }

  /** scopedQuery + search + (admin) Standort filter + ordering. */
  private function filteredQuery(Request $request)
  {
    $user = Auth()->user();
    $query = $this->scopedQuery($user);

    if ($user->hasRole('Super_Admin') && $request->filled('location')) {
      $query->where('location', $request->input('location'));
    }

    // Every word must match at least one field, so "max muster" finds
    // Vorname "Max" + Nachname "Mustermann".
    $terms = preg_split('/\s+/', trim((string) $request->input('search', '')), -1, PREG_SPLIT_NO_EMPTY);
    // kurs/gruppe/branch/deaktivierungsdatum have no migration in this repo
    // (added to the DB by hand, the import writes them) - only search the
    // columns that actually exist, so a DB without them can't 500 here.
    $searchable = array_values(array_intersect(
      ['vorname', 'nachname', 'username', 'course', 'location', 'kurs', 'gruppe', 'branch', 'email'],
      Schema::getColumnListing('participant_ticket_tables')
    ));
    foreach ($terms as $term) {
      $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term) . '%';
      $query->where(function ($q) use ($like, $searchable) {
        foreach ($searchable as $col) {
          $q->orWhere($col, 'like', $like);
        }
        $q->orWhereHas('ticket', function ($t) use ($like) {
          $t->where('done_by', 'like', $like);
        });
      });
    }

    return $query->orderByDesc('created_at')->orderByDesc('id');
  }

  /** Flat, already-formatted row for the page and every export. */
  private function rowPayload($p)
  {
    return [
      'id' => $p->id,
      'vorname' => $p->vorname,
      'nachname' => $p->nachname,
      'username' => $p->username,
      'password' => $p->password,
      'course' => $p->course,
      'location' => $p->location,
      // "Erstellt am" = the ticket's creation date, "Erledigt am" = when
      // the participant row was created (the import) - same as the old table.
      'ticket_created_at' => $p->ticket ? Carbon::parse($p->ticket->created_at)->format('d-m-Y') : '',
      'created_at' => $p->created_at ? $p->created_at->format('d-m-Y') : '',
      'done_by' => $p->ticket->done_by ?? '',
      'kurs' => $p->kurs,
      'gruppe' => $p->gruppe,
      'branch' => $p->branch,
      'deaktivierungsdatum' => $p->deaktivierungsdatum,
    ];
  }

    public function index2()
    {
      $user = Auth()->user();
      if(auth()->user()->hasRole('Super_Admin')){
        $participants = ParticipantTicketTable::with('ticket_dir')
            ->where('created_at', '>=', now()->subMonths(12))
            ->get();
        return $participants;
      }
      else {
      $participants = ParticipantTicketTable::where('location',$user->ort)
            ->where('created_at', '>=', now()->subMonths(12))
            ->orderBy('created_at','DESC')
            ->get();
      }
      return view ('tickets.participant.index',compact('participants'));
    }


    // public function participant_delete($id)
    // {
    //   $participant_row = ParticipantTicketTable::findOrFail($id);
    //   $participant_row -> forceDelete(); 
 
    // }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ParticipantTicketTable  $participantTicketTable
     * @return \Illuminate\Http\Response
     */
    public function show(ParticipantTicketTable $participantTicketTable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ParticipantTicketTable  $participantTicketTable
     * @return \Illuminate\Http\Response
     */
    public function edit(ParticipantTicketTable $participantTicketTable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ParticipantTicketTable  $participantTicketTable
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParticipantTicketTable $participantTicketTable)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ParticipantTicketTable  $participantTicketTable
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParticipantTicketTable $participant)
    {
      $participant ->Delete();
      return redirect()->back();
    }
}
