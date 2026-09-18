<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use App\Permission;
use DB;
class RoleController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
function __construct()
{
$this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
$this->middleware('permission:role-create', ['only' => ['create','store']]);
$this->middleware('permission:role-edit', ['only' => ['edit','update']]);
$this->middleware('permission:role-delete', ['only' => ['destroy']]);
}


/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(Request $request)
{
// Was Role::paginate(10) + a Pagination.vue footer - switched to the full
// list per the 2026-09-17 "every table sortable/searchable/paginated,
// client-side by default" retrofit; Roles/Index.vue now does its own
// sort/search/15-per-page via useDataTable instead of a server round trip
// per page.
$roles = Role::orderBy('id','DESC')->get();
return \Inertia\Inertia::render('Roles/Index', [
  'roles' => $roles,
]);
}
/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
  $permissionsByCategory = Permission::with('category')->orderBy('name')->get()
    ->groupBy(function ($permission) {
      return optional($permission->category)->name ?? 'Sonstige';
    });
return \Inertia\Inertia::render('Roles/Create', [
  'permissionsByCategory' => $permissionsByCategory,
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
'name' => 'required|unique:roles,name',
'permission' => 'required',
]);

$role = Role::create(['name' => $request->input('name')]);
$role->syncPermissions($request->input('permission'));

$sucMsg = array(
  'message' => 'Rolle erfolgreich hinzugefügt',
  'alert-type' => 'success'
);
return redirect()->route('roles.index')->with($sucMsg);
}
/**
* Display the specified resource.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function show($id)
{
$role = Role::find($id);
$rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
->where("role_has_permissions.role_id",$id)
->get();
return view('roles.show',compact('role','rolePermissions'));
}
/**
* Show the form for editing the specified resource.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function edit($id)
{
$role = Role::find($id);
$permissionsByCategory = Permission::with('category')->orderBy('name')->get()
  ->groupBy(function ($permission) {
    return optional($permission->category)->name ?? 'Sonstige';
  });
return \Inertia\Inertia::render('Roles/Edit', [
  'role' => $role,
  'permissionsByCategory' => $permissionsByCategory,
  'checkedPermissionIds' => $role->permissions->pluck('id'),
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
'name' => 'required',
'permission' => 'required',
]);
$role = Role::find($id);
$role->name = $request->input('name');
$role->save();
$role->syncPermissions($request->input('permission'));
$sucMsg = array(
  'message' => 'Erfolgreich bearbeitet',
  'alert-type' => 'success'
);
return redirect()->route('roles.index')->with($sucMsg);
}
/**
* Remove the specified resource from storage.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function destroy($id)
{
DB::table("roles")->where('id',$id)->delete();
return redirect()->route('roles.index')
->with('success','Role deleted successfully');
}
}

