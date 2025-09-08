<?php

namespace App\Http\Controllers;

use App\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
  public function index()
  {
    $devices = Device::all();
    return view('devices.index', compact('devices'));
  }

  public function create()
  {
    return view('devices.create');
  }

  public function store(Request $request)
  {
    $device = Device::create($request->all());
    return redirect()->route('devices.index');
  }

  public function show(Device $device)
  {
    return view('devices.show', compact('device'));
  }

  public function edit(Device $device)
  {
    return view('devices.edit', compact('device'));
  }

  public function update(Request $request, Device $device)
  {
    $device->update($request->all());
    return redirect()->route('devices.index');
  }

  public function destroy(Device $device)
  {
    $device->delete();
    return redirect()->route('devices.index');
  }
}
