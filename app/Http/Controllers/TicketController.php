<?php

namespace App\Http\Controllers;

use PDF;
use App\Gart;
use App\User;
use Response;
use App\Korso;
use App\Place;
use App\Ticket;
use App\InvRoom;
use App\License;
use App\Problem;
use App\CityNote;
use App\Employee;
use App\Handwerk;
use App\InvItems;
use App\Location;
use App\Reminder;
use Carbon\Carbon;
use App\Termination;
use App\TicketStatus;
use App\Standortbesuch;
use App\TicketPriority;
use App\EquipmentProblem;
use Illuminate\Http\Request;
use App\ParticipantTicketTable;
use Laravelista\Comments\Comment;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantTicketImport;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Validators\ValidationException;

class TicketController extends Controller
{

  function __construct()
  {
    $this->middleware('role:Super_Admin|Verwaltung')->only('index');
  }

  public function landing()
  {
    $user = Auth::user();

    if ($user) {
        if ($user->hasRole('Korso_Admin')) {
          return redirect()->route('korso.dashboard');
        }

        if ($user->hasAnyRole(['Super_Admin', 'HR'])) {
          $licenses = License::orderByRaw('CASE WHEN valid IS NULL THEN 0 ELSE 1 END DESC')
            ->orderBy('valid', 'ASC')
            ->get();
          $month = Carbon::now()->addDays(30);
          $week = Carbon::now()->addDays(7);
          $terminations = Termination::orderBy('exit', 'ASC')->get();
          return view('wilkommen', compact('user', 'licenses', 'month', 'week', 'terminations'));
        }
    }

    return redirect()->route('ticket.landingPage');
  }

  public function landingPage()
  {
    // Retrieve user and other shared data
    list($user, $users, $now, $admins) = User::getAll();
    $datum = Standortbesuch::find(1);

    // Prepare the cards based on the user's roles.
    $cards = [];

    if ($user) {
        // IT Tickets and Korso Tickets are visible by users with the "Verwaltung" role.
        if ($user->hasRole('Verwaltung')) {
          $cards[] = [
            'title' => 'IT Tickets',
            'url'   => route('ticket.index'),      // Adjust this route name as needed
            'color' => 'primary'                  // IT Tickets use Bootstrap Primary
          ];
          $cards[] = [
            'title' => 'Korso Tickets',
            'url'   => route('korso_index'),    // Adjust this route name as needed
            'color' => 'korso'                  // Korso Tickets use Bootstrap Success (green)
          ];
        }

        // Handwerk Tickets are visible by users with the "handwerk" role.
        if ($user->hasRole('Verwaltung')) {
          $cards[] = [
            'title' => 'Handwerk Tickets',
            'url'   => route('handwerk_index'), // Adjust this route name as needed
            'color' => 'handwerk'                     // Handwerk Tickets use Bootstrap Info (blue)
          ];
        }
    }

    // Determine Bootstrap column width based on the number of cards:
    $cardsCount = count($cards);
    if ($cardsCount === 3) {
      $colWidth = 4; // 3 cards in a row (col-md-4 each)
    } elseif ($cardsCount === 2) {
      $colWidth = 6; // 2 cards in a row (col-md-6 each)
    } else {
      $colWidth = 12; // 1 card takes the full width
    }

    return view('tickets.landing', compact('user', 'now', 'users', 'datum', 'cards', 'colWidth'));
  }


  //! index Ticketanfrage main page//
  public function index()
  {
    list($user, $users, $now, $admins) = User::getAll();
    $datum = Standortbesuch::find(1);
    return view('tickets.index', compact('user', 'now', 'users', 'datum'));
  }
  //! Ticket computer //
  public function computer_all()
  {
    return view('tickets.computer.all');
  }
  public function softwareRequest()
  {
    list($user, $users, $now) = User::getAll();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.computer.softwareRequest', compact('user', 'now', 'users', 'computers'));
  }
  public function softwareInstall()
  {
    list($user, $users, $now) = User::getAll();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.computer.softwareinstall', compact('user', 'now', 'users', 'computers'));
  }
  public function softwareError()
  {
    list($user, $users, $now) = User::getAll();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.computer.softwareerror', compact('user', 'now', 'users', 'computers'));
  }
  public function peripheralRequest()
  {
    list($user, $users, $now) = User::getAll();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.computer.peripheralRequest', compact('user', 'now', 'users', 'computers'));
  }
  public function hardwareRequest()
  {
    list($user, $users, $now) = User::getAll();
    $machines = Gart::where('id', '2')->orwhere('id', '3')->orwhere('id', '4')->orwhere('id', '5')
      ->orwhere('id', '13')->orwhere('id', '15')->orwhere('id', '18')->orwhere('id', '17')->orwhere('id', '6')->get();
    return view('tickets.computer.hardwareRequest', compact('user', 'now', 'users', 'machines'));
  }
  public function pc_problems()
  {
    list($user, $users, $now) = User::getAll();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.computer.pc_problems', compact('user', 'now', 'users', 'computers'));
  }
  public function printer_in_out()
  {
    list($user, $users, $now) = User::getAll();
    $rooms = InvRoom::with('location')->get();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.computer.printer_in_out', compact('user', 'now', 'users', 'computers'));
  }
  public function other()
  {
    list($user, $users, $now) = User::getAll();
    $rooms = InvRoom::with('location')->get();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.computer.other', compact('user', 'now', 'users', 'computers'));
  }

  //! Ticket printer //
  public function printer_all()
  {
    return view('tickets.printer.all');
  }

  public function scanner()
  {
    list($user, $users, $now) = User::getAll();
    $rooms = InvRoom::with('location')->get();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.printer.scanner', compact('user', 'now', 'users', 'computers'));
  }
  public function scannerNew()
  {
    list($user, $users, $now) = User::getAll();
    $rooms = InvRoom::with('location')->get();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.printer.scanner_new', compact('user', 'now', 'users', 'computers'));
  }
  public function functuality()
  {
    list($user, $users, $now) = User::getAll();
    $rooms = InvRoom::with('location')->get();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.printer.functuality', compact('user', 'now', 'users', 'computers'));
  }
  public function errors()
  {
    list($user, $users, $now) = User::getAll();
    $rooms = InvRoom::with('location')->get();
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    return view('tickets.printer.errors', compact('user', 'now', 'users', 'computers'));
  }

  //! Ticket users //
  public function users_all()
  {
    return view('tickets.users.all');
  }

  public function employee()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.users.employee', compact('user', 'now', 'users'));
  }

  public function participant()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.users.participant', compact('user', 'now', 'users'));
  }
  public function emailForward()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.users.emailForward', compact('user', 'now', 'users'));
  }
  public function users_others()
  {
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.users.usersOthers', compact('user', 'now', 'users', 'computers'));
  }
  public function users_namechange()
  {
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.users.nameChange', compact('user', 'now', 'users', 'computers'));
  }
  public function users_loginProblem()
  {
    $computers = InvItems::where('gart_id', '2')->orwhere('gart_id', '3')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.users.loginProblem', compact('user', 'now', 'users', 'computers'));
  }

  //! Ticket telephone //
  public function telephone_all()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.telephone.all', compact('user', 'now', 'users'));
  }
  public function tel_changes()
  {
    $rooms = InvRoom::with('location')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.telephone.tel_changes', compact('user', 'now', 'users'));
  }
  public function tel_changes_location()
  {
    $rooms = InvRoom::with('location')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.telephone.telChangeLocation', compact('user', 'now', 'users'));
  }
  public function tel_changes_name()
  {
    $rooms = InvRoom::with('location')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.telephone.telChangeName', compact('user', 'now', 'users'));
  }
  public function tel_changes_number()
  {
    $rooms = InvRoom::with('location')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.telephone.telChangeNumber', compact('user', 'now', 'users'));
  }

  public function tel_problems()
  {
    $rooms = InvRoom::with('location')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.telephone.tel_problems', compact('user', 'now', 'users'));
  }
  public function projectorProblems()
  {
    $rooms = InvRoom::with('location')->get();
    list($user, $users, $now) = User::getAll();
    return view('tickets.projector.projector_problem', compact('user', 'now', 'users'));
  }
  //! Ticket Web //
  public function web_all()
  {
    list($user, $users, $now, $tickets) = User::getAll();
    return view('tickets.web.all', compact('user', 'now', 'users'));
  }
  public function terminal_tn()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.web.terminal_tn', compact('user', 'now'));
  }
  public function bbb()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.web.bbb', compact('user', 'now'));
  }
  public function vtiger()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.web.vtiger', compact('user', 'now'));
  }
  public function smt()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.web.smt', compact('user', 'now'));
  }
  public function firmenvz()
  {
    list($user, $users, $now) = User::getAll();
    return view('tickets.web.firmenvz', compact('user', 'now'));
  }
  //! Ajax requests //
  public function tel_in_room(Request $request)
  {
    $telephones = InvItems::where('room_id', $request->telephones)->where('gart_id', '15')->get();
    return $telephones;
  }
  //! Ajax requests //
  public function pro_in_room(Request $request)
  {
    $projectors = InvItems::where('room_id', $request->projectors)->where('gart_id', '13')->get();
    return $projectors;
  }
  public function problem_type(Request $request)
  {
    $ticket_id = $request->ticket_id;
    $problems = Problem::where('ticket_type_id', $ticket_id)->get();
    return response()->json(['problems' => $problems]);
  }

  public function problem_type_machine(Request $request)
  {
    $whatever = $request->problem_type_id;
    $problems = EquipmentProblem::where('problem_id', $whatever)->get();
    return response()->json(['problems' => $problems]);
  }


  public function dependant_forms(Request $request)
  {
    $problem_id = $request->form_id;
    $contactname = $request->searchbyname;
    $forms = Problem::where('ticket_type_id', $problem_id)->get();
    $searchByName = User::where('name', $contactname)->get();
    if (!empty($searchByName)) {
      return response()->json(['problem_id' => $problem_id, 'searchByName' => $searchByName]);
    };
    return response()->json(['problem_id' => $problem_id]);
  }

  public function address()
  {
    $places = Place::pluck('id', 'pnname')->toArray();
    $locations = Location::with('invrooms')->get()->toArray();
    return ['locations' => $locations, 'places' => $places];
  }

  public function printer_in_room(Request $request)
  {
    $printers = InvItems::where('room_id', $request->printers)->where('gart_id', '5')->get();
    return $printers;
  }

  public function generateCityTicketsPdf($cityName)
  {
    // Get the city by its name
    $city = Place::where('pnname', $cityName)->first();

    // Fetch open tickets (not deleted) for the selected city and also include tickets where 'on_location' is 1
    $openTickets = Ticket::with('invitem') // Eager load the 'invitem' relationship
      ->whereHas('subUser', function ($query) use ($cityName) {
        $query->where('ort', $cityName); // Ensure that the user's 'ort' matches the city name
      })
      ->whereNull('deleted_at') // Only tickets that are not deleted
      ->where(function ($query) {
        $query->where('on_location', 1); // Check if 'on_location' is set to 1
      })
      ->get();

    if ($openTickets->isEmpty()) {
      return redirect()->back()->with('error', 'No open tickets found for this city.');
    }

    // Load the view and pass the tickets data to it
    $pdf = PDF::loadView('tickets.city_pdf', compact('openTickets', 'city'));

    // Generate the PDF and download it
    return $pdf->download("open_tickets_$cityName.pdf");
  }

  public function store(Request $request)
  {
    $admins = User::role('Super_Admin')->get();
    $user = Auth()->user();
    $ticket = new Ticket;
    $ticket->submitter = $request->submitter;
    $ticket->priority_id = $request->priority;
    $ticket->tel_number = $request->tel_number;
    $ticket->custom_tel_number = $request->custom_tel_number;
    $ticket->problem_type = $request->problem_type;
    $ticket->gname_id = $request->searchcomputer;   //* searchcomputer -> gname_id //
    $ticket->searchsoftware = $request->searchsoftware;
    $ticket->software_name = $request->software_name;
    $ticket->software_reason = $request->software_reason;
    $ticket->pc_laptop_others = $request->pclaptopsonstiges; //*pc_laptop_others -> pclaptopsonsitges // 
    $ticket->keyboard = $request->keyboard;
    $ticket->mouse = $request->mouse;
    $ticket->speaker = $request->speaker;
    $ticket->headset = $request->headset;
    $ticket->webcam = $request->webcam;
    $ticket->monitor = $request->monitor;
    $ticket->other = $request->other;
    $ticket->geht_nicht_an = $request->geht_nicht_an;
    $ticket->blue = $request->blue;
    $ticket->black = $request->black;
    $ticket->slow_computer = $request->slow_computer;
    $ticket->web_cam_problem = $request->web_cam_problem;
    $ticket->head_set_problem = $request->head_set_problem;
    $ticket->lautsprecher_mal = $request->lautsprecher_mal;
    $ticket->keyboard_malfunction = $request->keyboard_malfunction;
    $ticket->mouse_mal = $request->mouse_mal;
    $ticket->slow_network = $request->slow_network;
    $ticket->no_network_drive = $request->no_network_drive;
    $ticket->laud_fan = $request->laud_fan;
    $ticket->scanner_wrong_folder = $request->scanner_wrong_folder;
    $ticket->scanner_not_working = $request->scanner_not_working;
    $ticket->scanner_myname_list = $request->scanner_myname_list;
    $ticket->location_id = $request->location_id;
    $ticket->room_id = $request->room_id;
    $ticket->printer_name = $request->printer_name;
    $ticket->gart_id = $request->searchmachine;
    $ticket->replication_id = $request->replication_id;
    $ticket->position_employee = $request->position_employee;
    $ticket->abteilung_employee = $request->abteilung_employee;
    $ticket->telephone_employee = $request->telephone_employee;
    $ticket->outlook = $request->outlook;
    $ticket->isplus = $request->isplus;
    $ticket->employee_lastname = $request->employee_lastname;
    $ticket->employee_firstname = $request->employee_firstname;
    $ticket->employee_required_at = $request->employee_required_at;
    $ticket->employee_finish_at = $request->employee_finish_at;
    $ticket->email_erfurt = $request->email_erfurt;
    $ticket->email_berlin = $request->email_berlin;
    $ticket->email_suhl = $request->email_suhl;
    $ticket->email_dresden = $request->email_dresden;
    $ticket->email_leipzig = $request->email_leipzig;
    $ticket->email_chemnitz = $request->email_chemnitz;
    $ticket->email_lorenz = $request->email_lorenz;
    $ticket->email_lasch = $request->email_lasch;
    $ticket->email_custom = $request->email_custom;
    $ticket->password_name = $request->password_name;
    $ticket->forgotten = $request->forgotten;
    $ticket->inaktiv = $request->inaktiv;
    $ticket->expiring_date = $request->expiring_date;
    $ticket->abgelaufen = $request->abgelaufen;
    $ticket->user_oldname = $request->user_oldname;
    $ticket->user_newname = $request->user_newname;
    $ticket->user_other_username = $request->user_other_username;
    $ticket->tel_target_place = $request->tel_target_place;
    $ticket->tel_target_room = $request->tel_target_room;
    $ticket->pro_target_place = $request->pro_target_place;
    $ticket->pro_target_room = $request->pro_target_room;
    $ticket->current_tel_name = $request->current_tel_name;
    $ticket->new_tel_name = $request->new_tel_name;
    $ticket->new_tel_number = $request->new_tel_number;
    $ticket->bbb_subject = $request->bbb_subject;
    $ticket->bbb_username = $request->bbb_username;
    $ticket->vtiger_subject = $request->vtiger_subject;
    $ticket->vtiger_username = $request->vtiger_username;
    $ticket->smt_subject = $request->smt_subject;
    $ticket->smt_username = $request->smt_username;
    $ticket->firmen_subject = $request->firmen_subject;
    $ticket->firmen_username = $request->firmen_username;
    $ticket->terminal_name = $request->terminal_name;
    $ticket->terminal_datev = $request->terminal_datev;
    $ticket->terminal_lexware = $request->terminal_lexware;
    $ticket->terminal_expiry = $request->terminal_expiry;
    $ticket->forward_on = $request->forward_on;
    $ticket->forward_from = $request->forward_from;
    $ticket->forward_required_at = $request->forward_required_at;
    $ticket->cancelForward = $request->cancelForward;
    $description = $request->notizen;

    if ($description) {
      $dom = new \DomDocument();
      $dom->loadHtml(utf8_decode($description), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      $images = $dom->getElementsByTagName('img');
      foreach ($images as $k => $img) {
        $data = $img->getAttribute('src');
        list($type, $data) = explode(';', $data);
        list($type, $data) = explode(',', $data);
        $data = base64_decode($data);
        $image_name = "/upload/" . time() . $k . '.png';
        $path = public_path() . $image_name;
        file_put_contents($path, $data);
        $img->removeAttribute('src');
        $img->setAttribute('src', $image_name);
      }
      $description = $dom->saveHTML();
      $ticket->notizen = $description;
    }



    $ticket->save();

    $notifications = [
      'title' => 'Neues Ticket',
      'ticket_id' => $ticket->id,
      'submitter' => $ticket->subUser->username,
      'problem_type' => $ticket->problem_type,
    ];

    Notification::send($admins, new TicketNotification($notifications));
    return redirect()->route('ticket.usertickets');
  }

  public function download_muster()
  {
    $path = public_path('Muster.xlsx');
    return response()->download($path);
  }


  public function store_participant(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'muster' => 'required|max:20000|mimes:xlsx,xls',
    ]);

    if ($validator->fails()) {
      return back()->withErrors($validator);
    }

    $admins = User::role('Super_Admin')->get();
    $ticket = new Ticket;
    $ticket->submitter = $request->submitter;
    $ticket->priority_id = $request->priority;
    $ticket->tel_number = $request->tel_number;
    $ticket->custom_tel_number = $request->custom_tel_number;
    $ticket->problem_type = $request->problem_type;
    $ticket->participant_location = $request->place_id;
    $ticket->participant_required_at = $request->participant_required_at;

    try {
      // Save the ticket first
      $ticket->save();

      // Then attempt the import
      Excel::import(new ParticipantTicketImport($ticket->id, $ticket->participant_location), $request->file('muster'));

      $notifications = [
        'title' => 'Neuer Teilnehmer',
        'ticket_id' => $ticket->id,
        'submitter' => $ticket->subUser->username,
        'problem_type' => $ticket->problem_type,
      ];
      Notification::send($admins, new TicketNotification($notifications));
      return redirect()->route('ticket.usertickets');
    } catch (ValidationException $e) {
      // Deleting the saved ticket since the import failed
      $ticket->delete();

      $failures = $e->failures();
      $errorMessages = [];
      foreach ($failures as $failure) {
        $errorMessages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
      }

      return back()->with('import_errors', $errorMessages);
    }
  }

  public function participant_username_update(Request $request)
  {
    $username = ParticipantTicketTable::where('id', $request->password_id)->first();
    $username->username = $request->username;
    $username->save();
    return $username;
  }

  public function usertickets(Request $request, $city = null)
  {
    $user = Auth()->user();

    // Korso Table
    $user = auth()->user();

    // ─────────────────────────────────────────
    //  build a list of member IDs:
    // ─────────────────────────────────────────
    // 1) grab all groups the user belongs to
    $groups = $user->sekGroups()->with('users')->get();

    // 2) collect their member IDs (plus the user’s own ID to be safe)
    $memberIds = $groups->flatMap(function ($g) {
      return $g->users->pluck('id');
    })->unique()->push($user->id)->all();

    // ─────────────────────────────────────────
    //  now fetch *all* Korso tickets from those IDs
    // ─────────────────────────────────────────
    $korsoTicket = Korso::with('subUser', 'assignedUser', 'ticket_status')
      ->whereIn('submitter', $memberIds)
      ->orderBy('updated_at', 'DESC')
      ->get();
    $korso_ma_users = User::role('korso_ma')->get();
    $assignedCount = Korso::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    $myDoneCount = Korso::onlyTrashed()->where(function ($query) use ($user) {
      $query->where('submitter', $user->id)->orWhere('assignedTo', $user->id);
    })->count();

    // Define which users have access to which cities
    $userCities = [
      1 => ['erfurt', 'dresden', 'berlin', 'leipzig', 'chemntiz', 'döbeln'],    //Ara
      63 => ['dresden', 'berlin', 'leipzig', 'chemntiz', 'döbeln'],             //Lehnert
      327 => ['dresden', 'berlin', 'leipzig', 'chemntiz', 'döbeln', 'erfurt', 'suhl'], //Fuierer
    ];

    $myTickets = Ticket::with('invitem.invroom.location.place')->with('printer.invroom.location.place')->where('submitter', $user->id)->orWhere('assignedTo', $user->id)->orderBy('updated_at', 'DESC')->get();

    $ticketsdone = Ticket::onlyTrashed()->where(function ($query) use ($user) {
      $query->where('submitter', $user->id)->orWhere('assignedTo', $user->id);
    })->count();

    $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();

    // Handwerk
    $cityHandwerkCounts = [];

    if (array_key_exists($user->id, $userCities)) {
      $cities = $userCities[$user->id];

      foreach ($cities as $cityName) {
        $cityHandwerkCounts[$cityName] = Handwerk::where('submitter_standort', $cityName)->count();
      }

      if ($city !== null && in_array($city, $cities)) {
        $cities = [$city];
      }

      $myHandwerkTickets = Handwerk::with('location.invrooms')
        ->whereIn('submitter_standort', $cities)
        ->orderBy('updated_at', 'ASC')->get();
      $myhandwerkTicketsCountCity = Handwerk::whereIn('submitter_standort', $cities)->count();
    } else {
      $myHandwerkTickets = Handwerk::with('location.invrooms')->where('submitter_standort', $user->ort)->orderBy('updated_at', 'ASC')->get();
      $myhandwerkTicketsCountCity = Handwerk::where('submitter_standort', $user->ort)->count();
    }

    $handwerkticketsdone = Handwerk::onlyTrashed()->where(function ($query) use ($user) {
      $query->where('submitter', $user->id);
    })->count();

    $myhandwerkTicketsCount = Handwerk::where('submitter', $user->id)->count();

    return view('tickets.usertickets', compact(
      'user',
      'myTickets',
      'myTicketsCount',
      'ticketsdone',
      'myHandwerkTickets',
      'handwerkticketsdone',
      'myhandwerkTicketsCount',
      'myhandwerkTicketsCountCity',
      'userCities',
      'cityHandwerkCounts',
      'korsoTicket',
      'korso_ma_users',
      'assignedCount',
      'myDoneCount'
    ));
  }


public function userticketshistory()
{
    $user = Auth::user();

    // 1) eager-load all SekGroups + their users
    $user->load('sekGroups.users');

    // 2) collect every member’s ID (plus the user’s own ID)
    $memberIds = $user->sekGroups
        ->flatMap(function($group) {
            return $group->users->pluck('id');
        })
        ->push($user->id)
        ->unique()
        ->all();

    // ———————————————————————————————
    // regular Ticket history (unchanged)
    // ———————————————————————————————
    $oldTickets = Ticket::onlyTrashed()
        ->with('invitem.invroom.location.place', 'printer.invroom.location.place')
        ->where(function ($q) use ($user) {
            $q->where('submitter',   $user->id)
              ->orWhere('assignedTo', $user->id);
        })
        ->orderBy('deleted_at', 'desc')
        ->get();

    // ———————————————————————————————
    // Korso history *for the whole group*:
    // ———————————————————————————————
    $oldKorsoTickets = Korso::onlyTrashed()
        ->with('doneByUser', 'subUser', 'ticket_status')
        ->whereIn('submitter', $memberIds)
        ->orderBy('deleted_at', 'desc')
        ->get();

    // update your counts to include group members, if you like
    $assignedCount = Korso::whereIn('submitter', $memberIds)
        ->orWhereIn('assignedTo',   $memberIds)
        ->count();

    $myDoneCount = Korso::onlyTrashed()
        ->whereIn('submitter', $memberIds)
        ->orWhereIn('assignedTo', $memberIds)
        ->count();

    // non-Korso counts stay the same
    $ticketsdone    = Ticket::onlyTrashed()
        ->where(function ($q) use ($user) {
            $q->where('submitter',   $user->id)
              ->orWhere('assignedTo', $user->id);
        })
        ->count();

    $myTicketsCount = Ticket::where('submitter',   $user->id)
        ->orWhere('assignedTo', $user->id)
        ->count();

    return view('tickets.userticketsdone', compact(
        'user',
        'oldTickets',
        'myTicketsCount',
        'ticketsdone',
        'oldKorsoTickets',
        'myDoneCount',
        'assignedCount'
    ));
}


  public function opentickets()
  {
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();
    $myTickets = Ticket::with('subUser')->whereNull('on_location')->orderBy('created_at', 'DESC')->get();
    $AllTicketsCount = Ticket::whereNull('on_location')->count();
    $UnassignedTicketsCount = Ticket::whereNull('assignedTo')->whereNull('on_location')->count();
    $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    $ticketCounts = [];
    foreach ($admins as $admin) {
      $ticketCounts[$admin->id] = Ticket::Where('assignedTo', $admin->id)->whereNull('on_location')->count();
      $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    }

    $cityTicketCounts = $this->getCityTicketCounts();

    return view('tickets.admins.open', compact(
      'user',
      'myTickets',
      'AllTicketsCount',
      'admins',
      'UnassignedTicketsCount',
      'myTicketsCount',
      'ticketCounts',
      'cityTicketCounts'
    ));
  }
  
  public function userTicketsAdmins($userId = null)
  {
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();
    $AllTicketsCount = Ticket::whereNull('on_location')->count();
    $userId = $userId ?? $user->id; // Use provided userId or authenticated user's ID
    $myTickets = Ticket::with('subUser')->where('assignedTo', $userId)->whereNull('on_location')->orderBy('created_at', 'DESC')->get();
    $UnassignedTicketsCount = Ticket::whereNull('assignedTo')->whereNull('on_location')->count();
    $ticketCounts = [];
    foreach ($admins as $admin) {
      $ticketCounts[$admin->id] = Ticket::Where('assignedTo', $admin->id)->whereNull('on_location')->count();
      $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    }
    $cityTicketCounts = $this->getCityTicketCounts();
    return view('tickets.admins.admins', compact(
      'user',
      'myTickets',
      'admins',
      'ticketCounts',
      'AllTicketsCount',
      'UnassignedTicketsCount',
      'myTicketsCount',
      'cityTicketCounts'
    ));
  }

  public function unassignedtickets()
  {
    $name = 'unassigned';
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();
    $myTickets = Ticket::whereNull('assignedTo')->whereNull('on_location')->orderBy('updated_at', 'DESC')->get();
    $AllTicketsCount = Ticket::whereNull('on_location')->count();
    $UnassignedTicketsCount = Ticket::whereNull('assignedTo')->whereNull('on_location')->count();
    $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    $ticketCounts = [];
    foreach ($admins as $admin) {
      $ticketCounts[$admin->id] = Ticket::Where('assignedTo', $admin->id)->whereNull('on_location')->count();
      $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    }
    $cityTicketCounts = $this->getCityTicketCounts();
    return view('tickets.admins.unassigned', compact(
      'user',
      'myTickets',
      'UnassignedTicketsCount',
      'admins',
      'myTicketsCount',
      'AllTicketsCount',
      'ticketCounts',
      'cityTicketCounts'

    ));
  }


  public function cityTickets($cityName)
  {
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();

    $myTickets = Ticket::whereHas('subUser', function ($query) use ($cityName) {
      return $query->where('ort', '=', $cityName);
    })->whereNotNull('on_location')->get();

    $AllTicketsCount = Ticket::whereNull('on_location')->count();
    $UnassignedTicketsCount = Ticket::whereNull('assignedTo')->whereNull('on_location')->count();
    $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    $city = Place::with('notes')->where('pnname', $cityName)->first();


    // Admin ticket counts
    $ticketCounts = [];
    foreach ($admins as $admin) {
      $ticketCounts[$admin->id] = Ticket::Where('assignedTo', $admin->id)->whereNull('on_location')->count();
    }

    // City ticket counts
    $cityTicketCounts = $this->getCityTicketCounts();

    return view('tickets.admins.city', compact(
      'user',
      'myTickets',
      'AllTicketsCount',
      'admins',
      'UnassignedTicketsCount',
      'myTicketsCount',
      'ticketCounts',
      'cityTicketCounts',
      'city'
    ));
  }

  private function getCityTicketCounts()
  {
    $cities = Place::whereNotIn('id', [1])->pluck('pnname');
    $counts = [];
    foreach ($cities as $city) {
      $counts[$city] = Ticket::whereHas('subUser', function ($query) use ($city) {
        return $query->where('ort', '=', $city);
      })->whereNotNull('on_location')->count();
    }
    return $counts;
  }

  public function tickethistory()
  {

    $name = 'ticket history';
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();
    $myTickets = Ticket::onlyTrashed()->take(200)->orderBy('created_at', 'DESC')->get();
    $done = Ticket::onlyTrashed()->count();
    $AllTicketsCount = Ticket::all()->count();
    $myTicketsCount = Ticket::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    return view('tickets.admins.tickethistory', compact('user', 'myTickets', 'done', 'admins', 'myTicketsCount', 'AllTicketsCount'));
  }

  public function show($id)
  {
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();
    $ticket_status = TicketStatus::all();
    $ticket_priority = TicketPriority::all();
    $ticket = Ticket::with('invitem.invroom.location.place')->with('printer.invroom.location.place')->with('subUser')->withTrashed()->findorFail($id);
    $blade_name = 'tickets.admins.view_ticket_blades.' . str_replace(' ', '', strtolower($ticket->problem_type)) . 'ticket';
    $not = $user->unreadNotifications()->where('data->id', $id)->first();
    if ($not) {
      $not->markAsRead();
    }

    $createdAt = Carbon::parse($ticket->created_at);
    $telNewRoom = InvRoom::where('id', $ticket->tel_target_room)->first();
    $telNewAddress = Location::where('id', $ticket->tel_target_place)->first();
    $teilnehmers = ParticipantTicketTable::where('ticket_id', $ticket->id)->get();
    return view('tickets.admins.showticket', compact(
      'ticket',
      'createdAt',
      'ticket_status',
      'ticket_priority',
      'telNewRoom',
      'telNewAddress',
      'blade_name',
      'teilnehmers',
      'admins'
    ));
  }

  public function allRead()
  {
    $user = Auth()->user();
    $user->unreadNotifications()->update(['read_at' => now()]);
    return redirect()->back();
  }

  public function assignedTo(Request $request)
  {
    $assigned = Ticket::where('id', $request->ticket_id)->first();
    $assigned->assignedTo = $request->assignedTo;
    $assigned->save();

    $admins = User::role('Super_Admin')->get();
    foreach ($admins as $admin) {
      $not = $admin->Notifications()->where('data->id', $request->ticket_id)->first();
      if ($not) {
        $not->markAsRead();
      }
    }

    $ticket = Ticket::where('id', $request->ticket_id)->first();
    $assignedTo = User::where('id', $ticket->assignedTo)->first();
    $notifications = [
      'title' => 'Zugewiesen an',
      'ticket_id' => $ticket->id,
      'submitter' => $ticket->subUser->username,
      'problem_type' => $ticket->problem_type,
    ];
    Notification::send($assignedTo, new TicketNotification($notifications));
    return $assigned;
  }
  public function ticketPriority(Request $request)
  {
    $priority = Ticket::where('id', $request->ticket_id)->first();
    $priority->priority_id = $request->priority;
    $priority->save();
    return $priority;
  }
  public function ticketStatus(Request $request)
  {
    $status = Ticket::where('id', $request->ticket_id)->first();
    $status->ticket_status_id = $request->status;
    $status->save();
    return $status;
  }
  public function admin_notes(Request $request)
  {
    $admin_notes = Ticket::where('id', $request->ticket_id)->first();
    $admin_notes->admin_notes = $request->adminNotes;
    $admin_notes->save();
    return $admin_notes;
  }

  public function employee_username(Request $request)
  {
    $employee_username = Ticket::where('id', $request->ticket_id)->first();
    $employee_username->employee_username = $request->employee_username;
    $employee_username->save();
    return $employee_username;
  }
  public function employee_password(Request $request)
  {
    $employee_password = Ticket::where('id', $request->ticket_id)->first();
    $employee_password->employee_password = $request->employee_password;
    $employee_password->save();
    return $employee_password;
  }
  public function employee_email(Request $request)
  {
    $employee_email = Ticket::where('id', $request->ticket_id)->first();
    $employee_email->employee_email = $request->employee_email;
    $employee_email->save();
    return $employee_email;
  }
  public function employee_ISusername(Request $request)
  {
    $employee_ISusername = Ticket::where('id', $request->ticket_id)->first();
    $employee_ISusername->employee_ISusername = $request->employee_ISusername;
    $employee_ISusername->save();
    return $employee_ISusername;
  }
  public function updateRemark(Request $request, $city)
  {
    $validCities = [
      'berlin',
      'berlin2',
      'dresden',
      'chemnitz',
      'leipzig',
      'suhl',
      'döbeln',
      'erfurt'
    ];

    if (!in_array($city, $validCities)) {
      return response()->json(['error' => 'Invalid city'], 400);
    }

    $remark = CityNote::find(1);
    $remark->{$city} = $request->input($city);
    $remark->save();

    return response()->json($remark);
  }
  public function on_location(Request $request)
  {
    Ticket::where('id', $request->ticket_id)->update(['on_location' => '1']);
  }
  public function on_location_reverse(Request $request)
  {
    Ticket::where('id', $request->ticket_id)->update(['on_location' => null]);
  }

  public function update(Request $request, Ticket $ticket)
  {
    //
  }

  public function destroy(Request $request, $id)
  {
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();
    $ticket = Ticket::findOrFail($id);
    $ticket->done_by = $user->username;
    $ticket->save();
    foreach ($admins as $admin) {
      $not = $admin->Notifications()->where('data->id', $id)->first();
      if ($not) {
        $not->markAsRead();
      }
    }
    $notifications = [
      'title' => 'Erledigt',
      'ticket_id' => $ticket->id,
      'date' => Carbon::parse($ticket->created_at)->locale('de_DE')->translatedFormat('d F Y H:i'),
      'submitter' => $ticket->subUser->username,
      'problem_type' => $ticket->problem_type,
    ];
    $submitter = $ticket->subUser;
    Notification::send($submitter, new TicketNotification($notifications));

    $ticket->delete();
    Comment::withTrashed()->where('commentable_id', $id)->restore();
    return redirect()->route('ticket.opentickets');
  }



  public function mitarbeitersave(Request $request, $id)
  {
    $user = Auth()->user();
    $admins = User::role('Super_Admin')->get();
    //$ticket = collect(Ticket::findOrFail($id)->toArray());
    $ticket = Ticket::with('location.place')->findOrFail($id);
    $ticket->done_by = $user->username;
    $ticket->save();
    foreach ($admins as $admin) {
      $not = $admin->Notifications()->where('data->id', $id)->first();
      if ($not) {
        $not->markAsRead();
      }
    }

    $employee = new Employee();
    $employee->empFirstName = $ticket->employee_firstname;
    $employee->empLastName = $ticket->employee_lastname;
    $employee->empUsername = $ticket->employee_username;
    $employee->empEmail = $ticket->employee_email;
    $employee->empPosition = $ticket->position_employee;
    $employee->empAbteilung = $ticket->abteilung_employee;
    $employee->empTelefon = $ticket->telephone_employee;
    $employee->empISplus = $ticket->isplus;
    $employee->location = $ticket->location->address;
    $employee->Ticketsubmitter = $ticket->subUser->username;
    $employee->save();


    if ($ticket->employee_finish_at) {
      $termination = new Termination();
      $termination->name = $ticket->employee_firstname . ' ' . $ticket->employee_lastname;
      $termination->location = $ticket->location->place->pnname;
      $termination->exit = $ticket->employee_finish_at->format('d-m-Y');
      $termination->is_active = true;
      $termination->save();
    }

    $notifications = [
      'title' => 'Neuer Mitarbeiter',
      'problem_type' => $ticket->problem_type,
      'ticket_id' => $ticket->id,
      'date' => Carbon::parse($ticket->created_at)->locale('de_DE')->translatedFormat('d F Y H:i'),
      'submitter' => $ticket->subUser->username,
      'employee_firstname' => $ticket->employee_firstname,
      'employee_lastname' => $ticket->employee_lastname,
      'position_employee' => $ticket->position_employee,
      'abteilung_employee' => $ticket->abteilung_employee,
      'telephone_employee' => $ticket->telephone_employee,
      'employee_username' => $ticket->employee_username,
      'employee_email' => $ticket->employee_email,
      'employee_address' => $ticket->location->address,
      'employee_city' => $ticket->location->place->pnname,
      'employee_required_at' => $ticket->employee_required_at->format('d-m-Y'),
      'isplus' => $ticket->isplus,
      'message' => $ticket->notizen,
    ];

    $all_receptions = collect([
      $ticket->email_berlin,
      $ticket->email_erfurt,
      $ticket->email_dresden,
      $ticket->email_chemnitz,
      $ticket->email_suhl,
      $ticket->email_leipzig,
      $ticket->email_lasch,
      $ticket->email_lorenz,
      $ticket->email_custom,
      $ticket->subUser->email, //! submitter 
      //'Alexander.Ockel@miqr.de',
      'Matthias.Kirchner@miqr.de',
      'Denise.Naue@miqr.de',
      'Patrick.Hertel@miqr.de',
      'Ara.Matoyan@miqr.de',
    ]);
    // Split custom emails by semicolon, trim each email
    $customEmails = explode(';', $ticket->email_custom);
    $customEmails = array_map('trim', $customEmails);

    // Merge and filter the collection
    $all_receptions = $all_receptions->merge($customEmails)->filter(function ($email) {
      return filter_var($email, FILTER_VALIDATE_EMAIL);
    })->unique();

    // Flatten the collection to an array of emails
    $reception = $all_receptions->toArray();

    // Send notifications
    Notification::route('mail', $reception)->notify(new TicketNotification($notifications));

    $ticket->delete();
    Comment::withTrashed()->where('commentable_id', $id)->restore();
    return redirect()->route('ticket.opentickets');
  }

  public function restore($id)
  {
    $admins = User::role('Super_Admin')->get();
    $ticket = Ticket::withTrashed()->find($id);
    $notifications = [
      'title' => 'Wiederhergestellt',
      'ticket_id' => $ticket->id,
      'date' => Carbon::parse($ticket->created_at)->locale('de_DE')->translatedFormat('d F Y H:i'),
      'submitter' => $ticket->subUser->username,
      'problem_type' => $ticket->problem_type,
    ];
    Comment::withTrashed()->where('commentable_id', $id)->restore();
    Notification::send($admins, new TicketNotification($notifications));
    $ticket->restore();
    return redirect()->route('ticket.opentickets');
  }

  public function forceDelete(Request $request, $id)
  {
    $admins = User::role('Super_Admin')->get();
    $ticket = Ticket::findOrFail($id);

    foreach ($admins as $admin) {
      $not = $admin->Notifications()->where('data->id', $id)->first();
      if ($not) {
        $not->delete();
      }
    }
    $ticket->forceDelete();
    return 'true';
  }



  //! video //
  public function video()
  {
    return view('video');
  }

  public function video_index()
  {
    $datum = Standortbesuch::find(1);
    return view('tickets.video_index', compact('datum'));
  }

  public function setReminder(Request $request)
  {
    $date = $request->input('date');
    $ticketId = $request->input('ticketId');
    $user = Auth()->user()->id;

    // Create a new reminder
    $reminder = new Reminder([
      'ticket_id' => $ticketId,
      'reminder_date' => $date,
      'user_id' => $user,
      'is_reminded' => false,
    ]);

    // Save the reminder in the database
    $reminder->save();

    return response()->json(['success' => true]);
  }
}
