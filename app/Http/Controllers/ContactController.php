<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Place;
use App\Contact;
use App\InvItems;
use App\InvRoom;
use App\User;
use App\Location;
use Auth;

class ContactController extends Controller
{
  function __construct()
  {
  // $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
  // $this->middleware('permission:role-create', ['only' => ['create','store']]);
  // $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
  // $this->middleware('permission:role-delete', ['only' => ['destroy']]);

  $this->middleware('role:Super_Admin')->only('index');
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $users = User::get()->toArray();
      $cities = Place::all();

      // $phone = InvItems::with('invroom.location.place')->with('invroom.users')->with('garts')->where('gart_id',15)
      // ->whereHas('invroom', function ($query){
      //   return $query->where('location_id', '=', 2);
      //   })->get()->toArray();
     // return $phone;
     
      return view('contacts.index',compact('users','cities'));
    }


    public function contacts_phones_in_location(Request $request)
    {
			$locationContacts = InvItems::with('invroom.location.place')->with('garts')->with('telUsers')->where('gart_id',15)
																->whereHas('invroom', function ($query) use ($request) {
																	return $query->where('location_id',$request->location_id);
																	})->get()->toArray();
      $locationAddress = Location::with('place')->where('id', $request->location_id)->first();
			return ['locationContacts' => $locationContacts, 'locationAddress' => $locationAddress];
    }
    
    public function contacts_phones_in_room(Request $request)
    {
			$locationContacts = InvItems::with('invroom.location.place')->with('garts')->with('telUsers')->where('gart_id',15)
																->whereHas('invroom', function ($query) use ($request) {
																	return $query->where('room_id',$request->room_id);
																	})->get()->toArray();
			return $locationContacts;
    }

    

    // public function dynamicAddresses(Request $request)
    // {
    //   $place_id = $request->place_id;
    //   $address = Location::where('place_id',$place_id)->get();
    //   return response()->json(['address'=>$address]);
    // }

    // public function dynamicrooms(Request $request)
    // {
    //   $address_id = $request->all();
    //   $rooms = InvRoom::where('location_id',$address_id)->get();
    //   return response()->json(['rooms'=>$rooms]);
    // }

    public function searchByName(Request $request)
    {
      $contactname = $request->searchbyname;
			$searchByName = User::where('name',$contactname)->get()->toArray();
      if (!empty($searchByName )) {
        return $searchByName;
      };
    }

    public function searchByUsername(Request $request)
    {
      $contactusername = $request->searchByUsername;
			$searchByUsername = User::where('username',$contactusername)->get()->toArray();
      if (!empty($searchByUsername )) {
        return $searchByUsername;
      };
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
     * @param  \App\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contact $contact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        //
    }
}
