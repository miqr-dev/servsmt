<?php

namespace App\Http\Controllers;

use App\Location;
use App\Place;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // Settings city list //
    public function cityList()
    {
      $cities = Place::all();
      return ['cities' => $cities];
    }

    // Settings new location store //
    public function addLocation(Request $request)
    {
      $address = New Location;
      $address->place_id = $request->pnname;
      $address->address = $request->address;
      $address->save();

      $sucMsg = array(
        'message' => 'Erfolgreich hinzugefügt ',
        'alert-type' => 'success'
    );
    return redirect()->back()->with($sucMsg);
      
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function edit(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        //
    }
}
