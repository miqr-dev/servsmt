<?php

namespace App\Http\Controllers;

use App\DeviceStatus;
use Illuminate\Http\Request;

class DeviceStatusController extends Controller
{
  public function index()
  {
    $statuses = DeviceStatus::all();
    return view('deviceStatus.index', compact('statuses'));
  }

  public function create()
  {
    return view('deviceStatus.create');
  }

  public function store(Request $request)
  {
    DeviceStatus::create($request->all());
    return redirect()->route('device-statuses.index');
  }

  public function show(DeviceStatus $deviceStatus)
  {
    return view('deviceStatus.show', compact('deviceStatus'));
  }

  public function edit(DeviceStatus $deviceStatus)
  {
    return view('deviceStatus.edit', compact('deviceStatus'));
  }

  public function update(Request $request, DeviceStatus $deviceStatus)
  {
    $deviceStatus->update($request->all());
    return redirect()->route('device-statuses.index');
  }

  public function destroy(DeviceStatus $deviceStatus)
  {
    $deviceStatus->delete();
    return redirect()->route('device-statuses.index');
  }
}
