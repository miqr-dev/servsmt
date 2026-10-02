<?php

namespace App\Http\Controllers;

use App\User;
use App\AdSyncLog;
use App\Jobs\SyncUserToActiveDirectory;
use App\Support\ActiveDirectoryWriteback;
use App\InvItems;
use App\Location;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

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

        // Only the profile fields - $request->all() let a user set any
        // fillable column (e.g. password, email, status) via the request.
        $input = collect($request->only([
          'position', 'abteilung', 'tel', 'fax', 'ort', 'straße', 'plz', 'title',
          'mobil', 'privat', 'email_privat', 'abschluss', 'office',
        ]))->map(fn ($v) => is_string($v) ? trim($v) : $v)->all();
        $target->update($input);     

        // AD sync: write the fields straight to the AD account
        // (config/ad_writeback.php, App\Jobs\SyncUserToActiveDirectory).
        // A failing AD never breaks the save - it is logged and retried.
        if (ActiveDirectoryWriteback::enabled()) {
          $log = AdSyncLog::create(['user_id' => $target->id, 'changed_by' => auth()->id(), 'status' => AdSyncLog::PENDING]);
          SyncUserToActiveDirectory::dispatch($log->id);
          $log->refresh();
          if ($log->status === AdSyncLog::SUCCESS) {
            return back()->with('success', 'Profil gespeichert und im Active Directory aktualisiert.');
          }
          if (in_array($log->status, [AdSyncLog::FAILED, AdSyncLog::NOT_FOUND], true)) {
            return back()->with('error', $log->status === AdSyncLog::FAILED
              ? 'Profil gespeichert, aber das Active Directory konnte nicht aktualisiert werden. Es wird automatisch erneut versucht.'
              : 'Profil gespeichert, aber kein passendes Active-Directory-Konto gefunden. Bitte die IT informieren.');
          }

          return back()->with('success', 'Profil gespeichert. Die Änderungen werden ins Active Directory übertragen.');
        }

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

