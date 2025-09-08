<?php

namespace App\Http\Controllers;

use App\InvLastNumber;
use Illuminate\Http\Request;
use App\Place;

class InvLastNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $places = Place::pluck('id','pnname')->toArray();
      $lastNumber = InvLastNumber::with('location')->orderBy('created_at','desc')->get()->unique('location_id')->toArray();
      return ['lastNumber' => $lastNumber,'places' => $places];
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
     * @param  \App\Inv_last_number  $inv_last_number
     * @return \Illuminate\Http\Response
     */
    public function show(Inv_last_number $inv_last_number)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Inv_last_number  $inv_last_number
     * @return \Illuminate\Http\Response
     */
    public function edit(Inv_last_number $inv_last_number)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Inv_last_number  $inv_last_number
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Inv_last_number $inv_last_number)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Inv_last_number  $inv_last_number
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inv_last_number $inv_last_number)
    {
        //
    }
}
