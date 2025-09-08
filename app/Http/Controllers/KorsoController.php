<?php

namespace App\Http\Controllers;

use PDF;
use App\User;
use App\Korso;
use App\Payer;
use App\Kcourse;
use App\KorsoItem;
use App\Massnahme;
use App\TicketStatus;
use App\KorsoAttachment;
use App\ZertifizierungItem;
use App\OnlinemarketingItem;
use Illuminate\Http\Request;
use Laravelista\Comments\Comment;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;

class KorsoController extends Controller
{

  public function dashboard()
  {
    $user = Auth::user();

    // Get all Korso_Ma users with assigned ticket count
    $korso_ma_users = User::role('Korso_ma')
      ->select('*')
      ->selectRaw('LOWER(name) as lower_name')
      ->withCount(['assignedTickets'])
      ->orderBy('lower_name', 'asc')
      ->orderBy('vorname', 'asc')
      ->get();

    Korso::whereNotIn('assignedTo', $korso_ma_users)
      ->update(['assignedTo' => null]);

    // Fetch unassigned tickets by default, sorted by priority
    $tickets = Korso::with(['subUser', 'assignedUser', 'ticket_status'])
      ->whereNull('assignedTo') // Default filter: Unassigned tickets
      ->orderByRaw("CASE priority 
                          WHEN 3 THEN 1 
                          WHEN 2 THEN 2 
                          ELSE 3 END") // Hoch (3) → Normal (2) → Niedrig (1)
      ->orderBy('created_at', 'desc')
      ->get();

    // Get counts
    $assignedCount = Korso::where('assignedTo', $user->id)->count();
    $unassignedCount = Korso::whereNull('assignedTo')->count();
    $openCount = Korso::whereNull('deleted_at')->count(); // Corrected open count
    $myDoneCount = Korso::onlyTrashed()->where('assignedTo', $user->id)->count();
    $allDoneCount = Korso::onlyTrashed()->count();



    return view('korso.dashboard', compact('user', 'tickets', 'korso_ma_users', 'assignedCount', 'unassignedCount', 'openCount', 'myDoneCount', 'allDoneCount'));
  }

  //! sideslide
  public function getTicketDetails($id)
  {
    $ticket = Korso::with([
      'subUser',
      'assignedUser',
      'ticket_status',
      'onlinemarketingItem',
      'zertifizierungItem',
      'massnahme',
      'kcourses.payer',
      'korsoItems',
      'korsoAttachments',
    ])->findOrFail($id);

    return view('korso.partials.ticket_details', compact('ticket'));
  }


  //! filter tickets
  public function filterTickets(Request $request)
  {
    $user = Auth::user();
    $query = Korso::with(['subUser', 'assignedUser', 'ticket_status']);

    if ($request->filter === 'assigned') {
      $query->where('assignedTo', $user->id);
    } elseif ($request->filter === 'unassigned') {
      $query->whereNull('assignedTo');
    } elseif ($request->filter === 'open') {
      $query->whereNull('deleted_at');
    } elseif ($request->filter === 'myDone') {
      $query->onlyTrashed()->where('assignedTo', $user->id);
    } elseif ($request->filter === 'allDone') {
      $query->onlyTrashed();
    }

    // If user_id is passed (for Korso_Admin), filter by assignedTo
    if ($request->has('user_id')) {
      $query->where('assignedTo', $request->user_id);
    }

    $korso_ma_users = User::role('korso_ma')->get();
    $tickets = $query
      ->orderByRaw("CASE priority 
                          WHEN 3 THEN 1 
                          WHEN 2 THEN 2 
                          ELSE 3 END")
      ->orderBy('created_at', 'desc')
      ->get();

    return view('korso.partials.tickets_table', compact('tickets', 'korso_ma_users'))->render();
  }


  //! Assignto Ajax
  public function assignUser(Request $request)
  {
    $ticket = Korso::find($request->ticket_id);

    if (!$ticket) {
      return response()->json(['message' => 'Ticket not found'], 404);
    }

    // Update the ticket's assignedTo (empty string or null means unassigned)
    $ticket->assignedTo = $request->user_id;
    $ticket->save();

    // If a valid user_id is provided and it's not the current authenticated user, send a notification.
    if (!empty($request->user_id)) {
      if ($request->user_id != auth()->id()) {
        $assignedUser = User::find($request->user_id);
        if ($assignedUser) {
          $notifications = [
            'title' => 'Zugewiesen an',
            'korso_id' => $ticket->id,
            'submitter' => $ticket->submitter_name,
            'problem_type' => $ticket->problem_type,
          ];
          Notification::send($assignedUser, new \App\Notifications\KorsoNotification($notifications));
        }
      }
      return response()->json(['message' => 'Benutzer erfolgreich zugewiesen.']);
    } else {
      // If no user id is selected, return a message that the ticket has been unassigned.
      return response()->json(['message' => 'Ticketzuweisung erfolgreich aufgehoben!']);
    }
  }

  public function index()
  {
    $cityCounts = Korso::select('submitter_standort', DB::raw('count(*) as total'))
      ->groupBy('submitter_standort')
      ->pluck('total', 'submitter_standort');
    $korso = User::role(['Korso', 'Korso_Admin'])->get();
    return view('korso.index', compact('cityCounts'));
  }

  public function checkIfUserIsException()
  {
    $exceptions = User::findMany([1, 4, 119, 63, 16]);
    return $exceptions->contains(auth()->user());
  }

  public function printmarketing()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    $payers = Payer::with('kcourses')->get();
    return view('korso.printmarketing.printmarketing', compact('user', 'now', 'isException', 'payers'));
  }
  public function onlinemarketing()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    $onlinemarketingItems = OnlinemarketingItem::all();
    return view('korso.onlinemarketing.onlinemarketing', compact('user', 'now', 'isException', 'onlinemarketingItems'));
  }
  public function zertifizierung()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    $massnahmes = Massnahme::orderBy('name', 'asc')->get();
    $zertifizierung_items = ZertifizierungItem::all();
    return view('korso.zertifizierung.zertifizierung', compact('user', 'now', 'isException', 'massnahmes', 'zertifizierung_items'));
  }

  public function create()
  {
    //
  }

  public function updateStatus(Request $request, $id)
  {
    $korso = Korso::findOrFail($id);
    $korso->ticket_status_id = $request->ticket_status_id;
    $korso->save();

    return response()->json(['message' => 'Status erfolgreich aktualisiert.']);
  }

  public function form_store_korso(Request $request)
  {
    $korso = new Korso();
    $korso->submitter_name     = $request->input('submitter_name');
    $korso->submitter          = $request->input('submitter');
    $korso->submitter_standort = $request->input('submitter_standort');
    $korso->submitter_adresse  = $request->input('submitter_adresse');
    $korso->priority           = $request->input('priority', 2);
    $korso->tel_number         = $request->input('tel_number');
    $korso->problem_type       = $request->input('problem_type');
    $korso->onlinemarketing_item = $request->input('onlinemarketing_item');
    $korso->zertifizierung_item_id = $request->input('zertifizierung_item_id'); // NEW
    $korso->massnahme_id          = $request->input('massnahme_id'); // NEW (nullable)
    $korso->location_id        = $request->input('location_id');
    $korso->problem_in_city        = $request->input('submitter_standort_exception');
    $korso->notizen            = $request->input('notizen');
    $korso->sek_group_id       = $request->input('sek_group_id') ?: null;
    $korso->save();

    // Get all Kcourses to check against
    $allKcourses = Kcourse::pluck('name')->toArray();

    // Parse selected items from JSON input
    $selectedItems = json_decode($request->input('selected_items'), true);

    if (!is_array($selectedItems)) {
      $selectedItems = [];
    }

    // Initialize Kcourses array
    $selectedKcourses = [];

    foreach ($selectedItems as $item) {
      $itemName = $item['name'] ?? 'Unknown';
      $quantity = $item['quantity'] ?? 1;

      // If the item is a Kcourse, store it in pivot table (not in KorsoItems)
      if (in_array($itemName, $allKcourses)) {
        $kcourse = Kcourse::where('name', $itemName)->first();
        if ($kcourse) {
          $selectedKcourses[$kcourse->id] = ['quantity' => $quantity];
        }
      }
      // Otherwise, store in KorsoItems
      else {
        $data = [
          'korso_id'  => $korso->id,
          'item_name' => $itemName,
          'quantity'  => $quantity,
        ];

        // If Visitenkarten, add details
        if ($itemName === 'Visitenkarten') {
          $data['details'] = json_encode([
            'name'      => $request->input('visitenkarte_name'),
            'adresse'      => $request->input('visitenkarte_adresse'),
            'email'     => $request->input('visitenkarte_email'),
            'telephone' => $request->input('visitenkarte_telephone'),
            'position'    => $request->input('visitenkarte_position'),
            'fax'       => $request->input('visitenkarte_fax'),
          ]);
        }

        KorsoItem::create($data);
      }
    }

    // Sync Kcourses (store in kcourse_korso pivot table)
    $korso->kcourses()->sync($selectedKcourses);

    // Save attachments (PDFs & images)
    if ($request->hasFile('attachments')) {
      foreach ($request->file('attachments') as $file) {
        $timestamp = now()->format('Y-m-d_H-i-s'); // Get current date-time
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // Extract file name
        $extension = $file->getClientOriginalExtension(); // Get file extension

        // Create a structured filename
        $filename = "{$timestamp}_{$originalName}.{$extension}";

        // Store the file in the public/uploads/korsos directory
        $path = $file->storeAs('uploads/korsos', $filename, 'public');

        KorsoAttachment::create([
          'korso_id'  => $korso->id,
          'file_path' => $path,
          'file_type' => $file->getClientMimeType(),
        ]);
      }
    }
    if ($korso->priority == 3) {
      $notifications = [
        'title' => 'Neues Korso Ticket',
        'korso_id' => $korso->id,
        'submitter' => $korso->submitter_name,
        'problem_type' => $korso->problem_type,
      ];

      $korsoUsers = User::role('Korso_ma')->get();
      foreach ($korsoUsers as $user)
        $user->notify(new \App\Notifications\KorsoNotification($notifications));
    }
    // 🔔 Onlinemarketing-spezifische Benachrichtigung
    if ($korso->problem_type === 'Onlinemarketing') {
      $onlinemarketingUser = User::find(163);
      if ($onlinemarketingUser) {
        $notifications = [
          'title' => 'Neues Korso Ticket',
          'korso_id' => $korso->id,
          'submitter' => $korso->submitter_name,
          'problem_type' => $korso->problem_type,
        ];

        $onlinemarketingUser->notify(new \App\Notifications\KorsoNotification($notifications));
      }
    }

    return redirect()->route('ticket.usertickets')->with('success', 'Korso ticket created successfully!');
  }

  public function show($id)
  {
    $korso = Korso::withTrashed()->findOrFail($id);
    // Eager load internal comments along with their authors
    $korso->load([
      'kcourses.payer',
      'korsoItems',
      'internalComments.user', // Load internal comments with the user who wrote them
      'onlinemarketingItem',
      'zertifizierungItem',
      'massnahme',
      'ticket_status',
    ]);

    auth()->user()->unreadNotifications()
      ->where('data->id', $korso->id)
      ->get()
      ->each(function ($notification) {
        $notification->markAsRead();
      });

    $korso_ma_users = User::role('Korso_ma')
      ->orderBy('name', 'asc')     // Sort by last name alphabetically
      ->orderBy('vorname', 'asc')  // Then sort by first name
      ->get();
    $ticket_statuses = TicketStatus::all();

    return view('korso.show', compact('korso', 'korso_ma_users', 'ticket_statuses'));
  }

  public function downloadPdf($id)
  {
    // Load the Korso ticket and related data.
    $korso = Korso::withTrashed()->findOrFail($id);
    $korso->load([
      'kcourses.payer',
      'korsoItems',
      'internalComments.user',
      'onlinemarketingItem',
      'zertifizierungItem',
      'massnahme',
      'ticket_status',
      'korsoAttachments'
    ]);

    // (Optional) Mark any related notifications as read if desired…
    // ...

    // Prepare additional data if needed
    $korso_ma_users = User::role('korso_ma')->get();
    $ticket_statuses = TicketStatus::all();

    // Generate the PDF using a dedicated Blade view (see Step 3)
    $pdf = PDF::loadView('korso.pdf', compact('korso', 'korso_ma_users', 'ticket_statuses'));

    // Return the PDF as a download. The file name can be customized.
    return $pdf->download('korso_ticket_' . $korso->id . '.pdf');
  }

  /**
   * Export Printmarketing summary as PDF.
   */
  public function exportPrintmarketingPdf(Request $request)
  {
    $tab = $request->input('tab', 'all');

    // 1) Define your category-key arrays (same as in management)
    $flyerKeys = [
  'AfA_Kompakt',
  'DRV_Kompakt',
  'Arbeitgeberflyer_Umschulung',
  'Arbeitgeberflyer_Weiterbildung',
  'Aktualisierung_APO',
  'Aktualisierung_PAA',
  'Aktualisierung_BUS',
  'Aktualisierung_ISO',
  'Aktualisierung_ISO_Kompakt',
  'Aktualisierung_IBO',
  'Aktualisierung_MWe_Kompakt',
  'Aktualisierung_Bewerbungstraining',
  'Aktualisierung_kbQ',
  'Aktualisierung_Umschulung_Kompakt',
  'Aktualisierung_KBM',
  'Aktualisierung_IK',
  'Aktualisierung_KiG',
  'Aktualisierung_K_ECom',
  'DRV_FOSI',
  'DRV_OSI',
  'DRV_BT_S',
  'DRV_VL_bbU',
  'DRV_bbU',
  'DRV_RVL',
  'DRV_RVL_intensiv',
  'DRV_Umschulung_Kompakt',
  'DRV_KBM',
  'DRV_IK',
  'DRV_KIG',
  'DRV_K_ECOM',
  'Berufssprachkurse_BAMF-DE_EN',
  'Berufssprachkurse_BAMF-DE_UA',
  'Berufssprachkurse_BAMF-DE_AR',
  'Berufssprachkurse_BAMF-DE_ES',
  'BAMF_Deutsch_DE_EN',
  'BAMF_Deutsch_DE_UA',
  'BAMF_Deutsch_DE_AR',
  'BAMF_Deutsch_DE_ES',
  'BAMF_Alpha_DE_EN',
  'BAMF_Alpha_DE_UA',
  'BAMF_Alpha_DE_AR',
  'BAMF_Alpha_DE_ES',
  'BAMF_Zweit_DE_EN',
  'BAMF_Zweit_DE_UA',
  'BAMF_Zweit_DE_AR',
  'BAMF_Zweit_DE_ES',
  'BAMF_Gering_DE_EN',
  'BAMF_Gering_DE_UA',
  'BAMF_Gering_DE_AR',
  'BAMF_Gering_DE_ES',
  'MAPO_DE_EN',
  'MAPO_DE_UA',
  'MISO_DE_EN',
  'MISO_DE_UA',
  'MISO_MISO-K mit JobBSK',
  'MIBO_DE_EN',
  'MIBO_DE_UA',
  'Willkommensmappe',
  'Willkommensmappe_freie_MA',
  'Organigramm',
  'Erstellung_Anzeige',
  'Vorlage_Schuelerausweise',
  'Vorlage_Namensschilder',
  'Vorlage_Ansprechpartner'
    ];
    $giveawayKeys = [
      'City_Cards_Do_you_speak_German',
      'City_Cards_Salad',
      'City_Cards_Sauerkraut',
      'City_Cards_Smiley',
      'City_Cards_Egg',
      'Tragetaschen',
      'Einkaufswagenloeser',
      'Pflastermaeppchen',
      'Fruchtgummi',
      'Kugelschreiber'
    ];
    $stationeryKeys = [
      'Visitenkarten',
      'Glueckwunschkarte_Alles_Gute',
      'Glueckwunschkarte_blanco',
      'GA_Mappen',
      'Zeugnismappe',
      'Zeugnismappe_Deutschkurse',
      'Zeugnispapier',
      'A5_Ringblock',
      'A4_Schreibblock_Streifen',
      // 'A4_Schreibblock_Sprache',
      'USB_Stick',
      'Notizblock_PostIt_Stift',
      'Versandtasche_Fenster_C4',
      'Versandtasche_Fenster_C5',
      'Versandtasche_Fenster_DL',
      'Versandtasche_ohne_Fenster_C4',
      'Versandtasche_ohne_Fenster_C5',
      'Versandtasche_ohne_Fenster_DL',
      'Block_A6'
    ];
    $signageKeys = ['Beklebung', 'Beschilderung', 'Plakate'];
    $messeKeys = [
      'Anmeldung_Messe',
      'Ausstattung_Messe',
      'Rollup_Anzahl',
      'Plakate_Anzahl',
      'Messestand',
      'Beach_Flag',
      'Sonstiges'
    ];

    // 2) Build base query
    $query = DB::table('korso_items')
      ->join('korsos', 'korso_items.korso_id', '=', 'korsos.id')
      ->join('locations', 'korsos.location_id', '=', 'locations.id')
      ->where('korsos.problem_type', 'Printmarketing')
      ->whereNull('korsos.deleted_at') // <-- THIS LINE EXCLUDES DONE TICKETS
      ->select('korso_items.item_name', 'locations.address as location_name', DB::raw('SUM(korso_items.quantity) as total'))
      ->groupBy('korso_items.item_name', 'locations.address');

    // 3) If a single tab, filter by its keys
    if ($tab !== 'all') {
      $map = [
        'flyer'     => $flyerKeys,
        'giveaways' => $giveawayKeys,
        'stationery' => $stationeryKeys,
        'signage'   => $signageKeys,
        'messe'     => $messeKeys,
      ];
      if (isset($map[$tab])) {
        $query->whereIn('korso_items.item_name', $map[$tab]);
      }
    }

    $summary = $query->get();

    $doneDetails = KorsoItem::with('korso.location')
      ->whereHas('korso', function ($q) {
        $q->where('problem_type', 'Printmarketing');
      })
      ->where('ordered', true)
      ->get();

    $doneMap = [];
    foreach ($doneDetails as $d) {
      $loc = $d->korso->location->address;
      $doneMap[$d->item_name][$loc] = true;
    }

    // 4) Render simplified PDF view inline
    $pdf = PDF::loadView('korso.Printmarketing.printmarketing_pdf', [
      'summary'       => $summary,
      'tab'           => $tab,
      'flyerKeys'     => $flyerKeys,
      'giveawayKeys'  => $giveawayKeys,
      'stationeryKeys' => $stationeryKeys,
      'signageKeys'   => $signageKeys,
      'messeKeys'     => $messeKeys,
      'doneMap'        => $doneMap,
    ]);

    return $pdf->stream("printmarketing_summary_{$tab}.pdf");
  }


  public function uploadAttachment(Request $request, $id)
  {
    $korso = Korso::findOrFail($id);

    // Validate file(s) if needed. Here you can enforce file size limits or mimetypes.
    $request->validate([
      'attachments.*' => 'required|file|max:5120', // maximum 5MB per file
    ]);

    if ($request->hasFile('attachments')) {
      foreach ($request->file('attachments') as $file) {
        // Create a unique filename
        $timestamp = now()->format('Y-m-d_H-i-s');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = "{$timestamp}_{$originalName}.{$extension}";

        // Store the file in the public/uploads/korsos directory
        $path = $file->storeAs('uploads/korsos', $filename, 'public');

        // Save attachment info to the database
        KorsoAttachment::create([
          'korso_id'  => $korso->id,
          'file_path' => $path,
          'file_type' => $file->getClientMimeType(),
        ]);
      }
    }

    return response()->json([
      'success' => true,
      'message' => 'Dateien wurden erfolgreich hochgeladen!'
    ]);
  }

  public function deleteAttachment(Request $request, $korsoId, $attachmentId)
  {
    // Retrieve the attachment
    $attachment = KorsoAttachment::findOrFail($attachmentId);

    // Optionally verify that the attachment belongs to the given Korso ticket
    if ($attachment->korso_id != $korsoId) {
      return response()->json(['message' => 'Ungültige Operation'], 403);
    }

    // Check that the user has the required role
    if (!auth()->user()->hasRole('Korso_ma')) {
      return response()->json(['message' => 'Keine Berechtigung'], 403);
    }

    // Delete the file from the storage disk
    if (\Storage::disk('public')->exists($attachment->file_path)) {
      \Storage::disk('public')->delete($attachment->file_path);
    }

    // Delete the database record
    $attachment->delete();

    return response()->json(['message' => 'Anhang wurde erfolgreich gelöscht']);
  }


  public function edit(Korso $korso)
  {
    //
  }

  public function update(Request $request, Korso $korso)
  {
    //
  }

  public function fullDestroy($id)
  {
    try {
      $ticket = Korso::withTrashed()->findOrFail($id); // in case it's soft-deleted
      $ticket->forceDelete();

      return response()->json('true');
    } catch (\Exception $e) {
      return response()->json('false', 500);
    }
  }

  public function markAsDone(Korso $korso)
  {
    if (!$korso) {
      return response()->json(['message' => 'Ticket not found'], 404);
    }
    $korso->korsoItems()->update(['ordered' => true]);
    $korso->done_by = auth()->id();
    $korso->ticket_status_id = 3; // Set ticket status to "Erledigt"
    $korso->save();
    $korso->delete(); // Soft delete the ticket
    Comment::withTrashed()->where('commentable_id', $korso)->restore();

    // Get the user who submitted the ticket using the subUser relationship
    $submitterUser = $korso->subUser;

    if ($submitterUser) {
      $notificationData = [
        'title'       => 'Erledigt', // New title to identify this notification type
        'korso_id'    => $korso->id,
        'submitter'   => $korso->submitter_name,
        'problem_type' => $korso->problem_type,
      ];

      if ($korso->sek_group_id && $korso->sekGroup) {
        Notification::route('mail', $korso->sekGroup->email)
          ->notify(new \App\Notifications\KorsoNotification($notificationData));
      } else {
        $submitterUser = $korso->subUser;
        if ($submitterUser) {
          $submitterUser->notify(new \App\Notifications\KorsoNotification($notificationData));
        }
      }
    }

    return response()->json(['message' => 'Ticket marked as done successfully']);
  }

  public function destroy(Korso $korso)
  {

    return $korso;
    if (!$korso) {
      return response()->json(['message' => 'Ticket not found'], 404);
    }

    // Set the 'done_by' field to the ID of the authenticated user
    $korso->done_by = auth()->id();
    $korso->save();

    $korso->delete();

    DatabaseNotification::where('data->id', $korso->id)
      ->whereNull('read_at')
      ->update(['read_at' => now()]);

    return response()->json(['message' => 'Ticket deleted successfully']);
  }

  public function restore($id)
  {
    $korso = Korso::withTrashed()->findOrFail($id);
    // Restore Laravelista Comments linked to this Korso
    Comment::withTrashed()->where('commentable_id', $id)->restore();

    $korso->restore();
    $korso->korsoItems()->update(['ordered' => false]);

    // Reset the done_by field
    $korso->done_by = null;
    $korso->ticket_status_id = 8; // Set ticket status to "Wiederhergestellt"

    $korso->save();

    // Prepare notification data with a new title for restoration
    $notificationData = [
      'title'        => 'Wiederhergestellt',
      'korso_id'     => $korso->id,
      'submitter'    => $korso->submitter_name,
      'problem_type' => $korso->problem_type,
    ];

    // Determine who performed the restore
    $restoredBy = auth()->user();

    if ($restoredBy->id == $korso->submitter) {
      // Restored by the submitter:
      // If an assigned user exists, notify that user; otherwise, fallback to user with ID 37.
      if ($korso->assignedTo) {
        $assignedUser = $korso->assignedUser;
        if ($assignedUser) {
          $assignedUser->notify(new \App\Notifications\KorsoNotification($notificationData));
        }
      } else {
        $fallbackUser = \App\User::find(39);
        if ($fallbackUser) {
          $fallbackUser->notify(new \App\Notifications\KorsoNotification($notificationData));
        }
      }
    } else {
      // Restored by Korso_ma or the assigned user:
      // Notify the submitter.
      if ($korso->sek_group_id && $korso->sekGroup) {
        Notification::route('mail', $korso->sekGroup->email)
          ->notify(new \App\Notifications\KorsoNotification($notificationData));
      } else {
        $submitterUser = $korso->subUser;
        if ($submitterUser) {
          $submitterUser->notify(new \App\Notifications\KorsoNotification($notificationData));
        }
      }
    }

    return redirect()->back()->with('success', 'Korso und zugehörige Kommentare wurden wiederhergestellt.');
  }

  public function userManagement()
  {
    $users = User::all();
    $korsoMaUsers = User::role('Korso_ma')->get();
    return view('korso.user-management', compact('users', 'korsoMaUsers'));
  }
  public function assignRole(Request $request)
  {
    $userIds = $request->user_ids;

    foreach ($userIds as $id) {
      $user = User::find($id);
      if ($user && !$user->hasRole('Korso_ma')) {
        $user->assignRole('Korso_ma');
      }
    }

    return redirect()->route('user.management')->with('success', 'Rolle(n) erfolgreich zugewiesen.');
  }
  public function removeRole(Request $request)
  {
    $user = User::findOrFail($request->user_id);
    $user->removeRole('Korso_ma');

    return redirect()->route('user.management')->with('success', 'Rolle erfolgreich entfernt.');
  }

  public function printmarketingManagement()
  {
    // Grouped summary by item and location (using address column)
    $summary = DB::table('korso_items')
      ->join('korsos', 'korso_items.korso_id', '=', 'korsos.id')
      ->join('locations', 'korsos.location_id', '=', 'locations.id')
      ->where('korsos.problem_type', 'Printmarketing')
      ->whereNull('korsos.deleted_at') // <-- THIS LINE EXCLUDES DONE TICKETS
      ->select(
        'korso_items.item_name',
        'locations.address as location_name',
        DB::raw('SUM(korso_items.quantity) as total')
      )
      ->groupBy('korso_items.item_name', 'locations.address')
      ->get();

    // Detailed list of all KorsoItems for Printmarketing tickets
    $details = KorsoItem::with(['korso.location'])
      ->whereHas('korso', function ($query) {
        $query->where('problem_type', 'Printmarketing');
      })
      ->get();

    return view('korso.Printmarketing.management', compact('summary', 'details'));
  }

  public function toggleOrdered(KorsoItem $korsoItem)
  {
    $korsoItem->ordered = !$korsoItem->ordered;
    $korsoItem->save();
    return response()->json(['ordered' => $korsoItem->ordered]);
  }
}
