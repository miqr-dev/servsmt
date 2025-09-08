<?php

namespace App\Http\Controllers;

use App\User;
use App\SekGroup;
use Illuminate\Http\Request;

class SekGroupController extends Controller
{
  public function index()
  {
    // paginate to keep the list manageable
    $groups = SekGroup::orderBy('name')->paginate(15);

    return view('sek-groups.index', compact('groups'));
  }

  public function create()
  {
    //
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name'  => 'required|string|unique:sek_groups,name',
      'email' => 'required|email|unique:sek_groups,email',
    ]);

    SekGroup::create($data);

    return redirect()
      ->route('sek-groups.index')
      ->with('success', 'Sek group created.');
  }

  public function show(SekGroup $sekGroup)
  {
    //
  }

  public function edit(SekGroup $sekGroup)
  {
    //
  }

  public function update(Request $request, SekGroup $sekGroup)
  {
    //
  }

  public function destroy(SekGroup $sekGroup)
  {
    // this will also cascade-delete pivot records
    $sekGroup->delete();

    return redirect()
      ->route('sek-groups.index')
      ->with('success', 'Sek group “' . $sekGroup->name . '” deleted.');
  }

  public function editMembers(SekGroup $group)
  {
    // all users (or filter to secretaries only)
    $users = User::orderBy('name')->get();
    // IDs of current members
    $memberIds = $group->users()->pluck('users.id')->toArray();

    return view('sek-groups.membership', compact('group', 'users', 'memberIds'));
  }

  // public function updateMembers(Request $request, SekGroup $group)
  // {
  //   $request->validate([
  //     'users'   => 'array',
  //     'users.*' => 'exists:users,id',
  //   ]);

  //   // sync pivot table
  //   $group->users()->sync($request->input('users', []));

  //   return redirect()
  //     ->route('sek-groups.members.edit', $group)
  //     ->with('success', 'Membership updated.');
  // }

  public function addMembers(Request $request, SekGroup $group)
  {
    $request->validate([
      'user_ids'   => 'required|array',
      'user_ids.*' => 'exists:users,id',
    ]);

    // attach without detaching existing
    $group->users()->syncWithoutDetaching($request->user_ids);

    return response()->json(['status' => 'ok']);
  }

  /**
   * Detach a user from this SekGroup.
   */
  public function removeMember(Request $request, SekGroup $group)
  {
    $request->validate([
      'user_id' => 'required|exists:users,id',
    ]);

    $group->users()->detach($request->user_id);

    return redirect()
      ->route('sek-groups.members.edit', $group)
      ->with('success', 'Mitglied entfernt.');
  }
}
