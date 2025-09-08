<?php

namespace App\Http\Controllers;

use App\Job;
use App\Device;
use App\User;
use App\Ticket;
use App\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
  public function index()
  {
    $projects = Project::orderBy('created_at', 'DESC')->get();

    return view('projects.index', compact('projects'));
  }
  public function create()
  {
    $admins =  User::role('Super_Admin')->get();
    return view('projects.create', compact('admins'));
  }
  public function store(Request $request)
  {
    $project = new Project();
    $project->name = $request->name;
    $project->description = $request->description;
    $project->start_date = $request->start_date;
    $project->end_date = $request->end_date;
    $project->assignedTo = $request->assignedTo;
    $project->who_created = Auth()->user()->id;
    $project->status = $request->status;
    $project->save();
    return redirect()->route('projects.show', $project);
  }

public function storeDevices(Request $request, $projectId)
{
    $project = Project::findOrFail($projectId);
    $devices = $request->input('device_ids', []);
    $quantities = $request->input('quantities', []);
    $notes = $request->input('notes', []);
    $statuses = $request->input('statuses', []);

    foreach ($devices as $index => $deviceId) {
        $quantity = $quantities[$index] ?? 0;
        $note = $notes[$index] ?? '';
        $status = $statuses[$index] ?? (App\Enums\DeviceStatus::INACTIVE)->value;

        if ($quantity > 0) {
            // Check if the pivot entry exists with the exact quantity, note, and status
            $pivot = $project->devices()->where('device_id', $deviceId)->wherePivot('quantity', $quantity)->wherePivot('note', $note)->wherePivot('status', $status)->first();

            if ($pivot) {
                // If exact pivot exists, increment the quantity
                $project->devices()->updateExistingPivot($deviceId, [
                    'quantity' => DB::raw("quantity + $quantity")
                ]);
            } else {
                // Attach a new record if it does not exist
                $project->devices()->attach($deviceId, [
                    'quantity' => $quantity,
                    'note' => $note,
                    'status' => $status
                ]);
            }
        }
    }

    return redirect()->route('projects.show', $projectId)->with('success', 'Devices updated successfully.');
}



  public function show(Project $project)
  {
    $tickets = Ticket::all(); // You might want to filter these based on some logic
    $devices = Device::all(); // Assuming you're also listing devices in the same view
    return view('projects.show', compact('project', 'tickets', 'devices'));
  }

public function attachTickets(Request $request, $projectId)
{
    $project = Project::findOrFail($projectId);
    $ticketIds = $request->ticket_ids;

    // Assuming the relationship is many-to-many
    $project->tickets()->sync($ticketIds);

    return back()->with('success', 'Tickets attached successfully.');
}

  public function detachTicket($projectId, $ticketId)
{
    $project = Project::findOrFail($projectId);
    $project->tickets()->detach($ticketId);

    return back()->with('success', 'Ticket detached successfully.');
}


  public function edit(Project $project)
  {
    //
  }

  public function update(Request $request, Project $project)
  {
    //
  }

  public function destroy(Project $project)
  { {
      $project->delete();

      return redirect()->route('projects.index')
        ->with('success', 'Projekt erfolgreich gelöscht');
    }
  }
}
