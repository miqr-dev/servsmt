<?php

namespace App\Http\Controllers;

use App\User;
use App\InvItems;
use App\Location;
use App\Exports\UserExport;
use App\Imports\UserImport;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class SettingController extends Controller
{

  function __construct()
    {
    // $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
    // $this->middleware('permission:role-create', ['only' => ['create','store']]);
    // $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
    // $this->middleware('permission:role-delete', ['only' => ['destroy']]);

    $this->middleware('role:Super_Admin')->only('index');
    }


  public function firstpage($id) 
    {
      $user = User::findorfail($id);
      return view ('settings.firstpage',compact('user'));
    }

    

  public function firstupdate(Request $request,$id) 
    {
      // Profile page: everyone may update THEIR OWN profile (Super_Admin any).
      abort_unless((int) $id === (int) auth()->id() || auth()->user()->isSuperAdmin(), 403);
      $target = User::findOrFail($id);
      // Name, Vorname and Benutzername come from AD and are read-only on the
      // page - take them from the record, never from the request.
      $request->merge(['vorname' => $target->vorname, 'name' => $target->name]);

      $this->validate($request, [
        'position' => 'required',
        'abteilung' => 'required',
        'tel' => 'required',
        'ort' => 'required',
        'straße' => 'required',
        'plz' => 'required',
        'vorname' => 'required',
        'name' => 'required',
        'title' => 'required',
        ]);

        // Row for storage/app/user/updateuser.csv (AD sync) - same columns as
        // before (see App\Exports\UserExport headings), values trimmed.
        $v = fn ($k) => is_string($request->input($k)) ? trim($request->input($k)) : $request->input($k);
        $row = [
          $target->username, $v('position'), $v('abteilung'), $v('tel'), $v('fax'), $v('ort'), $v('straße'),
          $v('plz'), $v('title'), $v('vorname'), $v('name'), $v('mobil'), $v('privat'), $v('email_privat'),
          $v('abschluss'), $v('office'),
        ];
        $importuser = [$row];
        try {
          // append to the existing file (first sheet), if there is one
          $existing = Excel::toArray(new UserImport, 'updateuser.csv','user');
          $importuser = $existing[0];
          $importuser[] = $row;
        }
        catch (\Exception $e) {
          ;
        }
        Excel::store(new UserExport($importuser), 'updateuser.csv','user');

        // Only the profile fields - $request->all() let a user set any
        // fillable column (e.g. password, email, status) via the request.
        $input = collect($request->only([
          'position', 'abteilung', 'tel', 'fax', 'ort', 'straße', 'plz', 'title',
          'mobil', 'privat', 'email_privat', 'abschluss', 'office',
        ]))->map(fn ($v) => is_string($v) ? trim($v) : $v)->all();
        $target->update($input);     
        // Back to the profile page with a toast (was: redirect to home).
        return back()->with('success', 'Profil gespeichert.');
    }
		//** Settings index **//

    
		public function index()
		{
			return \Inertia\Inertia::render('Settings/Index');
		}

		/**
		 * The "Inventur Einstellungen" quick-add (city/address/room) modals on
		 * the old settings.index view aren't converted yet - that's inventory
		 * location management, not Settings/Roles/Users. Rather than dropping
		 * that functionality, it stays reachable here unchanged until its own
		 * conversion pass.
		 */
		public function legacyIndex()
		{
			return view('settings.index');
		}


}

