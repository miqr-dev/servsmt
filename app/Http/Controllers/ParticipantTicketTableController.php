<?php

namespace App\Http\Controllers;

use App\Ticket;
use Illuminate\Http\Request;
use App\ParticipantTicketTable;
use Illuminate\Support\Facades\Redirect;

class ParticipantTicketTableController extends Controller
{

  function __construct()
  {
    $this->middleware('role:Super_Admin|Teilnehmer_Info|Verwaltung')->only('index');
  }
public function index()
{
    $user = Auth()->user();

    $participantsQuery = ParticipantTicketTable::query()
        ->select('participant_ticket_tables.*')
        ->with(['ticket' => function ($query) {
            $query->select('id', 'created_at', 'done_by');
        }])
        ->where('created_at', '>=', now()->subMonths(6));

    if ($user->hasRole('Super_Admin')) {
        $participants = $participantsQuery->orderByDesc('created_at')->get();
    } else {
        if ($user->ort === 'Berlin') {
            if ($user->straße === 'Prenzlauer Promenade 28') {
                $participantsQuery->where('location', 'Berlin-PP');
            } elseif ($user->straße === 'Trachenbergring 93') {
                $participantsQuery->where('location', 'Berlin-TBR');
            } else {
                $participantsQuery->where('location', 'Berlin');
            }
        } else {
            $participantsQuery->where('location', $user->ort);
        }

        $participants = $participantsQuery->orderByDesc('created_at')->get();
    }

    return view('tickets.participant.index', compact('participants'));
}




    public function index2()
    {
      $user = Auth()->user();
      if(auth()->user()->hasRole('Super_Admin')){
        $participants = ParticipantTicketTable::with('ticket_dir')
            ->where('created_at', '>=', now()->subMonths(6))
            ->get();
        return $participants;
      }
      else {
      $participants = ParticipantTicketTable::where('location',$user->ort)
            ->where('created_at', '>=', now()->subMonths(6))
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
