<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;

class UserController extends Controller
{

  // Profil (user menu > Profil). Converted from user/profile.blade.php
  // (2026-10-02). Saving still goes to SettingController@firstupdate, which
  // updates the user and appends the row to storage/app/user/updateuser.csv
  // (the export the AD signature/attribute sync reads).
  public function profile()
  {
      $user = Auth()->user();
      $fields = ['title', 'vorname', 'name', 'username', 'email', 'position', 'abteilung', 'tel', 'fax',
        'ort', 'straße', 'plz', 'mobil', 'privat', 'email_privat', 'abschluss', 'office'];
      $profile = ['id' => $user->id];
      foreach ($fields as $f) {
          $profile[$f === 'straße' ? 'strasse' : $f] = is_string($user->$f) ? trim($user->$f) : $user->$f;
      }

      return \Inertia\Inertia::render('Profile/Edit', ['profile' => $profile]);
  }
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index(Request $request)
  {
  $data = User::with('roles')->get();
  $collectionOfRoles = Role::pluck('name')->toArray();
  return \Inertia\Inertia::render('Users/Index', [
    'users' => $data,
    'collectionOfRoles' => $collectionOfRoles,
  ]);
  }
  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
  $roles = Role::pluck('name','name')->all();
  return \Inertia\Inertia::render('Users/Create', [
    'roles' => $roles,
  ]);
  }
  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
  $this->validate($request, [
  'name' => 'required',
  'roles' => 'required'
  ]);
  $input = $request->all();
  $input['password'] = Hash::make($input['password']);
  $user = User::create($input);
  $user->assignRole($request->input('roles'));
  return redirect()->route('users.index')
  ->with('success','User created successfully');
  }
  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {
  $user = User::find($id);
  return view('users.show',compact('user'));
  }
  /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
  $user = User::find($id);
  $roles = Role::pluck('name','name')->all();
  $userRole = $user->roles->pluck('name')->values();
  return \Inertia\Inertia::render('Users/Edit', [
    'user' => $user,
    'roles' => $roles,
    'userRoles' => $userRole,
  ]);
  }
  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, $id)
  {
    $this->validate($request, [
    'roles' => 'required'
    ]);
    $input = $request->all();
    $user = User::find($id);
    $user->update($input);
    DB::table('model_has_roles')->where('model_id',$id)->delete();
    $user->assignRole($request->input('roles'));

    $sucMsg = array(
      'message' => 'Erfolgreich bearbeitet',
      'alert-type' => 'success'
    );
    return redirect()->route('users.index')->with($sucMsg);
  }
  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)

  {
  User::find($id)->delete();

  $sucMsg = array(
    'message' => 'Erfolgreich bearbeitet',
    'alert-type' => 'success'
  );
  return redirect()->route('users.index')
  ->with($sucMsg);
  }
}