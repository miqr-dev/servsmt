<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Permissioncategory;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index']]);
        $this->middleware('permission:permission-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::with('category')->orderBy('id', 'DESC')->paginate(15);
        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Permissioncategory::pluck('name', 'id');
        return view('permissions.create', compact('categories'));
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
            'name' => 'required|unique:permissions,name',
            'permissioncategory_id' => 'nullable|exists:permissioncategories,id',
        ]);

        Permission::create([
            'name' => $request->input('name'),
            'guard_name' => 'web',
            'permissioncategory_id' => $request->input('permissioncategory_id'),
        ]);

        $sucMsg = [
            'message' => 'Permission erfolgreich hinzugefügt',
            'alert-type' => 'success',
        ];
        return redirect()->route('permissions.index')->with($sucMsg);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $permission = Permission::with(['category', 'roles'])->findOrFail($id);
        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $categories = Permissioncategory::pluck('name', 'id');
        return view('permissions.edit', compact('permission', 'categories'));
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
        $permission = Permission::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|unique:permissions,name,' . $permission->id,
            'permissioncategory_id' => 'nullable|exists:permissioncategories,id',
        ]);

        $permission->name = $request->input('name');
        $permission->permissioncategory_id = $request->input('permissioncategory_id');
        $permission->save();

        $sucMsg = [
            'message' => 'Erfolgreich bearbeitet',
            'alert-type' => 'success',
        ];
        return redirect()->route('permissions.index')->with($sucMsg);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Permission::findOrFail($id)->delete();

        $sucMsg = [
            'message' => 'Permission erfolgreich gelöscht',
            'alert-type' => 'success',
        ];
        return redirect()->route('permissions.index')->with($sucMsg);
    }
}
