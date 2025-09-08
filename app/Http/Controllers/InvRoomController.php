<?php

namespace App\Http\Controllers;

use App\InvRoom;
use Illuminate\Http\Request;
use App\Place;
use App\Location;

class InvRoomController extends Controller
{
    public function cityAddressList()
    {
      $places = Place::pluck('id','pnname')->toArray();
      $locations = Location::with('invrooms')->get()->toArray();
      return ['locations'=>$locations,'places'=>$places];
    }


    public function addRoom(Request $request)
    {
      $room = New InvRoom();
      $room -> location_id =  $request -> pnname;
      $room -> place_id = $request->place_id;
      $room -> rname = $request->rname;
      $room -> altrname = $request->altrname;
      $room->save();
      return redirect();
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
     * @param  \App\Inv_room  $inv_room
     * @return \Illuminate\Http\Response
     */
    public function show(Inv_room $inv_room)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Inv_room  $inv_room
     * @return \Illuminate\Http\Response
     */
    public function edit(Inv_room $inv_room)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Inv_room  $inv_room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Inv_room $inv_room)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Inv_room  $inv_room
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inv_room $inv_room)
    {
        //
    }
}