<?php

namespace App\Http\Controllers;

use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialTicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->id !== 16 && !$user->hasRole('Super_Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $cities = ['Dresden', 'Chemnitz', 'Berlin', 'Leipzig', 'Döbeln', 'Riesa'];

        $tickets = Ticket::whereIn('problem_type', ['Computer', 'Drucker', 'Benutzer', 'Telefon', 'Web'])
            ->where('ticket_status_id', '!=', 3)
            ->whereNull('deleted_at')
            ->whereHas('subUser', function ($query) use ($cities) {
                $query->whereIn('ort', $cities);
            })
            ->with('subUser', 'assignedUser', 'ticket_status')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('tickets.special.index', compact('tickets', 'user'));
    }
}
