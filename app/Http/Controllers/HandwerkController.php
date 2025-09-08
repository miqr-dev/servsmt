<?php

namespace App\Http\Controllers;


use PDF;
use App\User;
use App\Place;
use App\Handwerk;
use App\Location;
use Carbon\Carbon;
use App\HandwerkTodo;
use App\TicketStatus;
use App\TicketPriority;
use Illuminate\Http\Request;
use Laravelista\Comments\Comment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\HandwerkNotification;
use Illuminate\Support\Facades\Notification;

class HandwerkController extends Controller
{
  public function index()
  {
    $cityCounts = Handwerk::select('submitter_standort', DB::raw('count(*) as total'))
      ->groupBy('submitter_standort')
      ->pluck('total', 'submitter_standort');
    return view('handwerk.index', compact('cityCounts'));
  }

  public function openTicketsPDF($city)
  {
    $openHandwerks = Handwerk::where('submitter_standort', $city)
      ->whereNull('deleted_at')
      ->get();

    $pdf = PDF::loadView('handwerk.open_tickets_pdf', compact('openHandwerks', 'city'));

    return $pdf->download(ucfirst($city) . '_open_tickets.pdf');
  }

  public function showCity($city)
  {
    $handwerks = Handwerk::where('submitter_standort', $city)->get();
    $todos = HandwerkTodo::where('standort', $city)->get()->map(function ($todo) {
      $todo->updated_at_german = $todo->updated_at->format('d') . '.' .
        $todo->updated_at->locale('de')->monthName . '.' .
        $todo->updated_at->format('y H:i');
      return $todo;
    });

    return view('handwerk.city', ['city' => $city, 'handwerks' => $handwerks, 'todos' => $todos]);
  }
  public function storeTodo(Request $request, $city)
  {
    $todo = new HandwerkTodo;
    $todo->standort = $city;
    $todo->title = $request->title;
    $todo->body = $request->body;
    $todo->user_id = auth()->user()->id;
    $todo->save();

    // Load the user relationship to get the username
    $todo->load('submitter');

    // Get the updated_at date in German format
    $todo->updated_at_german = $todo->updated_at->format('d') . '.' .
      $todo->updated_at->locale('de')->monthName . '.' .
      $todo->updated_at->format('y H:i');

    // Add the user's username to the returned data
    $todo->username = $todo->submitter->username;

    return response()->json($todo);
  }

  public function checkIfUserIsException()
  {
    $exceptions = User::findMany([1, 4, 119, 63, 16]);
    return $exceptions->contains(auth()->user());
  }


  public function einrichtungsgegenstände()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    return view('handwerk.new.einrichtungsgegenstände', compact('user', 'now', 'isException'));
  }
  public function elektro()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    return view('handwerk.new.elektro', compact('user', 'now', 'isException'));
  }
  public function neustandort()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    return view('handwerk.new.neustandort', compact('user', 'now', 'isException'));
  }
  public function reparatur_elektro()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    return view('handwerk.reparatur.elektro', compact('user', 'now', 'isException'));
  }
  public function reparatur_mobiliar()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    return view('handwerk.reparatur.mobiliar', compact('user', 'now', 'isException'));
  }
  public function modifikation()
  {
    list($user, $users, $now) = User::getAll();
    $isException = $this->checkIfUserIsException();
    return view('handwerk.modification.modifikation', compact('user', 'now', 'isException'));
  }




  public function store(Request $request)
  {
    //
  }

  public function form_store_handwerk(Request $request)
  {

    $handwerk = new Handwerk();
    $handwerk->submitter_name = $request->submitter_name;
    $handwerk->submitter = $request->submitter;
    $handwerk->neustandort_room = $request->neustandort_room;

    $handwerk->submitter_standort = $request->submitter_standort_exception
      ? $request->submitter_standort_exception
      : $request->submitter_standort;

    $handwerk->submitter_adresse = $request->submitter_adresse;
    $handwerk->priority = $request->priority;
    $handwerk->tel_number = $request->tel_number;
    $handwerk->problem_type = $request->problem_type;
    $handwerk->location_id = $request->location_id;
    $handwerk->room_id = $request->room_id;
    $handwerk->schiebetafel = $request->schiebetafel;
    $handwerk->whiteboard = $request->whiteboard;
    $handwerk->kreidetafel = $request->kreidetafel;
    $handwerk->schreibtisch_TN_70x70 = $request->schreibtisch_TN_70x70;
    $handwerk->schreibtisch_TN_80x80 = $request->schreibtisch_TN_80x80;
    $handwerk->schreibtisch_TN_80x160 = $request->schreibtisch_TN_80x160;
    $handwerk->schreibtisch_DOZ_80x140 = $request->schreibtisch_DOZ_80x140;
    $handwerk->schreibtisch_DOZ_80x160 = $request->schreibtisch_DOZ_80x160;
    $handwerk->schreibtisch_DOZ_80x180 = $request->schreibtisch_DOZ_80x180;
    $handwerk->schreibtisch_MA_80x140 = $request->schreibtisch_MA_80x140;
    $handwerk->schreibtisch_MA_80x160 = $request->schreibtisch_MA_80x160;
    $handwerk->schreibtisch_MA_80x180 = $request->schreibtisch_MA_80x180;
    $handwerk->stehtisch = $request->stehtisch;
    $handwerk->gesprächstisch_rund = $request->gesprächstisch_rund;
    $handwerk->konferenztisch = $request->konferenztisch;
    $handwerk->couchtisch = $request->couchtisch;
    $handwerk->beistelltisch = $request->beistelltisch;
    $handwerk->ventilator = $request->ventilator;
    $handwerk->ventilator_qty = $request->ventilator_qty;
    $handwerk->kühlschrank = $request->kühlschrank;
    $handwerk->kühlschrank_qty = $request->kühlschrank_qty;
    $handwerk->geschirrspüler = $request->geschirrspüler;
    $handwerk->geschirrspüler_qty = $request->geschirrspüler_qty;
    $handwerk->kaffeemaschine = $request->kaffeemaschine;
    $handwerk->kaffeemaschine_qty = $request->kaffeemaschine_qty;
    $handwerk->schreibtischstuhl = $request->schreibtischstuhl;
    $handwerk->bürostuhl = $request->bürostuhl;
    $handwerk->stapelstühl = $request->stapelstühl;
    $handwerk->rollcontainer = $request->rollcontainer;
    $handwerk->standcontainer = $request->standcontainer;
    $handwerk->hochschrank = $request->hochschrank;
    $handwerk->ordnerhöhen_2 = $request->ordnerhöhen_2;
    $handwerk->ordnerhöhen_3 = $request->ordnerhöhen_3;
    $handwerk->hängeschrank = $request->hängeschrank;
    $handwerk->lamellenvorhang = $request->lamellenvorhang;
    $handwerk->rollo = $request->rollo;
    $handwerk->pinnwand = $request->pinnwand;
    $handwerk->bilder = $request->bilder;
    $handwerk->handtuchspender = $request->handtuchspender;
    $handwerk->toilettenpapierhalter = $request->toilettenpapierhalter;
    $handwerk->desinfektionsmittelspender = $request->desinfektionsmittelspender;
    $handwerk->barzeile = $request->barzeile;
    $handwerk->bar_Hochstühle = $request->bar_Hochstühle;
    $handwerk->küchenzeile = $request->küchenzeile;
    $handwerk->schiebetafel_qty = $request->schiebetafel_qty;
    $handwerk->whiteboard_qty = $request->whiteboard_qty;
    $handwerk->kreidetafel_qty = $request->kreidetafel_qty;
    $handwerk->schreibtisch_TN_70x70_qty = $request->schreibtisch_TN_70x70_qty;
    $handwerk->schreibtisch_TN_80x80_qty = $request->schreibtisch_TN_80x80_qty;
    $handwerk->schreibtisch_TN_80x160_qty = $request->schreibtisch_TN_80x160_qty;
    $handwerk->schreibtisch_DOZ_80x140_qty = $request->schreibtisch_DOZ_80x140_qty;
    $handwerk->schreibtisch_DOZ_80x160_qty = $request->schreibtisch_DOZ_80x160_qty;
    $handwerk->schreibtisch_DOZ_80x180_qty = $request->schreibtisch_DOZ_80x180_qty;
    $handwerk->schreibtisch_MA_80x140_qty = $request->schreibtisch_MA_80x140_qty;
    $handwerk->schreibtisch_MA_80x160_qty = $request->schreibtisch_MA_80x160_qty;
    $handwerk->schreibtisch_MA_80x180_qty = $request->schreibtisch_MA_80x180_qty;
    $handwerk->stehtisch_qty = $request->stehtisch_qty;
    $handwerk->gesprächstisch_rund_qty = $request->gesprächstisch_rund_qty;
    $handwerk->konferenztisch_qty = $request->konferenztisch_qty;
    $handwerk->couchtisch_qty = $request->couchtisch_qty;
    $handwerk->beistelltisch_qty = $request->beistelltisch_qty;
    $handwerk->schreibtischstuhl_qty = $request->schreibtischstuhl_qty;
    $handwerk->bürostuhl_qty = $request->bürostuhl_qty;
    $handwerk->stapelstühl_qty = $request->stapelstühl_qty;
    $handwerk->rollcontainer_qty = $request->rollcontainer_qty;
    $handwerk->standcontainer_qty = $request->standcontainer_qty;
    $handwerk->hochschrank_qty = $request->hochschrank_qty;
    $handwerk->ordnerhöhen_2_qty = $request->ordnerhöhen_2_qty;
    $handwerk->ordnerhöhen_3_qty = $request->ordnerhöhen_3_qty;
    $handwerk->hängeschrank_qty = $request->hängeschrank_qty;
    $handwerk->lamellenvorhang_qty = $request->lamellenvorhang_qty;
    $handwerk->rollo_qty = $request->rollo_qty;
    $handwerk->pinnwand_qty = $request->pinnwand_qty;
    $handwerk->bilder_qty = $request->bilder_qty;
    $handwerk->handtuchspender_qty = $request->handtuchspender_qty;
    $handwerk->toilettenpapierhalter_qty = $request->toilettenpapierhalter_qty;
    $handwerk->desinfektionsmittelspender_qty = $request->desinfektionsmittelspender_qty;
    $handwerk->barzeile_qty = $request->barzeile_qty;
    $handwerk->bar_Hochstühle_qty = $request->bar_Hochstühle_qty;
    $handwerk->küchenzeile_qty = $request->küchenzeile_qty;
    $handwerk->subject = $request->subject;
    $handwerk->custom_room = $request->custom_room;
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
      $handwerk->notizen = $description;
    }

    $handwerk->save();


    $notifications = [
      'title' => 'Neues Handwerk Ticket',
      'ticket_id' => $handwerk->id,
      'submitter' => $handwerk->submitter_name,
      'problem_type' => $handwerk->problem_type,
    ];


    // $furr = User::find(1);
    $furr = User::find(327);
    Notification::send($furr, new HandwerkNotification($notifications));

    $sucMsg = array(
      'message' => 'Ticket erfolgreich hinzugefügt',
      'alert-type' => 'success'
    );

    return redirect()->route('ticket.usertickets')->with($sucMsg);
  }

  public function room_list(Request $request, $city = null)
  {
    $placeName = $city ? $city : auth()->user()->ort;

    $place = Place::where('pnname', $placeName)->first(['id', 'pnname']);
    $placeId = $place ? $place->id : null;

    $locations = Location::where('place_id', $placeId)
      ->with('invrooms')
      ->get()
      ->toArray();

    return ['locations' => $locations, 'place' => $place];
  }


  public function create()
  {
    //
  }

  public function myHandwerks()
  {
    $user = Auth::user();
    $handwerks = Handwerk::where('submitter', $user->id)->get();

    return view('handwerk.my_handwerks', compact('handwerks'));
  }

  // public function hUserTicket()
  // {
  //   $user = Auth()->user();
  //   $myhandwerk = Handwerk::with('invroom.location.place')->where('submitter', $user->id)->orWhere('assignedTo', $user->id)->orderBy('updated_at', 'DESC')->get();
  //   $handwerkdone = Handwerk::onlyTrashed()->where(function ($query) use ($user) {
  //     $query->where('submitter', $user->id)->orWhere('assignedTo', $user->id);
  //   })->count();
  //   $myhandwerkcount = Handwerk::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
  //   return view('handwerk.userhandwerks', compact('user', 'myhandwerk', 'handwerkdone', 'myhandwerkcount'));
  // }

  public function show($id)
  {
    $user = Auth()->user();
    $admins = User::role('handwerk_admin')->get();
    $handwerk_status = TicketStatus::all();
    $handwerk_priority = TicketPriority::all();
    $handwerk = Handwerk::with('room.location.place')->with('subUser')->withTrashed()->findorFail($id);
    $blade_name = 'handwerk.layout.views.' . str_replace(' ', '', strtolower($handwerk->problem_type)) . 'ticket';
    $not = $user->unreadNotifications()->where('data->id', $id)->first();
    if ($not) {
      $not->markAsRead();
    }

    $createdAt = Carbon::parse($handwerk->created_at);
    return view('handwerk.show', compact(
      'handwerk',
      'createdAt',
      'handwerk_status',
      'handwerk_priority',
      'blade_name',
      'admins'
    ));
  }

  // public function admin_notes(Request $request)
  // {
  //   $admin_notes = Handwerk::where('id', $request->ticket_id)->first();
  //   $admin_notes->admin_notes = $request->adminNotes;
  //   $admin_notes->save();
  //   return $admin_notes;
  // }
  // public function assignedTo(Request $request)
  // {
  //   $assigned = Handwerk::where('id', $request->handwerkId)->first();
  //   $assigned->assignedTo = $request->assignedTo;
  //   $assigned->save();


  //   $handwerk = Handwerk::where('id', $request->handwerkId)->first();
  //   $assignedTo = User::where('id', $handwerk->assignedTo)->first();

  //   $notifications = [
  //     'title' => 'Zugewiesen an',
  //     'ticket_id' => $handwerk->id,
  //     'submitter' => $handwerk->subUser->username,
  //     'problem_type' => $handwerk->problem_type,
  //   ];
  //   Notification::send($assignedTo, new HandwerkNotification($notifications));
  //   return $assigned;
  // }

  public function assignedTo(Request $request)
{
    $assigned = Handwerk::where('id', $request->handwerkId)->first();
    $assigned->assignedTo = $request->assignedTo;
    $assigned->save();

    $handwerk = Handwerk::where('id', $request->handwerkId)->first();
    $assignedTo = User::where('id', $handwerk->assignedTo)->first();

    $notifications = [
        'title' => 'Zugewiesen an',
        'ticket_id' => $handwerk->id,
        'submitter' => $handwerk->subUser->username,
        'problem_type' => $handwerk->problem_type,
    ];

    if ($assignedTo->id == 1473 || $assignedTo->id == 14441 || $assignedTo->id == 1 || $assignedTo->id == 23192) {
        // Generate PDF
        $pdf = PDF::loadView('handwerk.ticket_details', compact('handwerk'));
        $pdfPath = storage_path('app/public/ticket_' . $handwerk->id . '.pdf');
        $pdf->save($pdfPath);

        // Add the path to notifications array
        $notifications['pdf_path'] = $pdfPath;
    }

    Notification::send($assignedTo, new HandwerkNotification($notifications));

    return $assigned;
}


  public function edit(Handwerk $handwerk)
  {
    //
  }

  public function update(Request $request, Handwerk $handwerk)
  {
    //
  }

  public function userhandwerkticketshistory()
  {
    $user = Auth()->user();
    $handwerkticketsdone = Handwerk::onlyTrashed()->with('room.location.place')->with('subUser')
      ->where(function ($query) use ($user) {
        $query->where('submitter', $user->id)
          ->orWhere('assignedTo', $user->id)
          ->orwhere('submitter_standort', $user->ort)
          ->orderBy('deleted_at', 'desc');
      })->latest()->get();

    $handwerkticketsdoneCount = Handwerk::onlyTrashed()->where(function ($query) use ($user) {
      $query->where('submitter', $user->id);
    })->count();
    $myhandwerkTicketsCountCity = Handwerk::onlyTrashed()->where(function ($query) use ($user) {
      $query->where('submitter_standort', $user->ort);
    })->count();
    $myhandwerkTicketsCount = Handwerk::where('submitter', $user->id)->orWhere('assignedTo', $user->id)->count();
    return view('handwerk.handwerkHistory', compact('user', 'handwerkticketsdone', 'handwerkticketsdone', 'myhandwerkTicketsCountCity', 'myhandwerkTicketsCount', 'handwerkticketsdoneCount'));
  }

  public function destroy(Request $request, $id)
  {
    $user = Auth()->user();
    $admins = User::role('handwerk_admin')->first();
    $handwerk = Handwerk::findOrFail($id);
    $handwerk->done_by = $user->username;
    $handwerk->save();

    $notifications = [
      'title' => 'Erledigt',
      'ticket_id' => $handwerk->id,
      'date' => Carbon::parse($handwerk->created_at)->locale('de_DE')->translatedFormat('d F Y H:i'),
      'submitter' => $handwerk->submitter_name,
      'problem_type' => $handwerk->problem_type,
    ];
    $submitter = $handwerk->subUser;
    Notification::send($submitter, new HandwerkNotification($notifications));

    $handwerk->delete();
    Comment::withTrashed()->where('commentable_id', $id)->restore();
    return redirect()->route('ticket.usertickets');
  }

  public function restore($id)
  {
    $admin = User::find(327)->first();

    $handwerk = Handwerk::withTrashed()->find($id);
    $notifications = [
      'title' => 'Wiederhergestellt',
      'ticket_id' => $handwerk->id,
      'date' => Carbon::parse($handwerk->created_at)->locale('de_DE')->translatedFormat('d F Y H:i'),
      'submitter' => $handwerk->subUser->username,
      'problem_type' => $handwerk->problem_type,
    ];
    Comment::withTrashed()->where('commentable_id', $id)->restore();
    Notification::send($admin, new HandwerkNotification($notifications));
    $handwerk->restore();
    return redirect()->route('ticket.usertickets');
  }

  public function ajaxDestroy(Request $request, $id)
{
    try {
        $user = auth()->user();
        $handwerk = Handwerk::findOrFail($id);

        $handwerk->done_by = $user->username;
        $handwerk->save();

        // Notify the submitter
        $notifications = [
            'title' => 'Erledigt',
            'ticket_id' => $handwerk->id,
            'date' => Carbon::parse($handwerk->created_at)->locale('de_DE')->translatedFormat('d F Y H:i'),
            'submitter' => $handwerk->submitter_name,
            'problem_type' => $handwerk->problem_type,
        ];

        $submitter = $handwerk->subUser;
        Notification::send($submitter, new HandwerkNotification($notifications));

        // Delete the handwerk and restore related comments
        $handwerk->delete();
        Comment::withTrashed()->where('commentable_id', $id)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Das Ticket wurde erfolgreich gelöscht.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Fehler beim Löschen des Tickets.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
