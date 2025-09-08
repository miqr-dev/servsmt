<?php

namespace App\Http\Controllers;

use App\Amg;
use App\Gart;
use App\User;
use App\Place;
use App\InvRoom;
use App\InvItems;
use App\Location;
use App\InvAbItem;
use Carbon\Carbon;
use App\InvMoveItem;
use App\InvSubRooms;
use App\InvLastNumber;
use App\TempInvAbItem;
use App\FinalInventory;
use App\UnorderedComputer;
use App\Exports\RoomExport;
use App\Imports\RoomImport;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Print_;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class InvAbItemController extends Controller
{
  public function __construct()
  {
    $this->middleware('role:Super_Admin|INV')->only('index');    
  }


/*************************************************************{{ Rename   }}******************************************************************************/
public function search_rename(Request $request)
{
  $search_text = strtoupper($_GET['search_rename']);
  $items = InvAbItem::with('garts')->Where(function ($query) use ($search_text) {
                                          $query->whereNull('ausdat')->where('invnr',$search_text);
                                          })->
                                          orWhere(function ($query) use ($search_text) {
                                              $query->whereNull('ausdat')->where('gname',strtoupper($search_text));
                                          })->first();
  $room = InvItems::with('invroom.location')->where('invnr',$items->invnr)->first();
  return ['items'=>$items,'room'=>$room];
}

public function searchCheckRename(Request $request)
{
  $data = $request->all();
  $check = InvAbItem::where('invnr',$data['search_rename'])->orwhere('gname',$data['search_rename'])->first();
  $check = InvAbItem::Where(function ($query) use ($data) {
                            $query->whereNull('ausdat')->where('invnr',strtoupper($data['search_rename']));
                            })->
                            orWhere(function ($query) use ($data) {
                                $query->whereNull('ausdat')->where('gname',strtoupper($data['search_rename']));
                            })->first();
  if ($check && $data['search_rename']!="") {
      echo "true";
  }else{
      echo "false";
  }
}

/**
 * Method rename update
 */
public function updateRename(Request $request)
{
  $items = InvAbItem::where('invnr',$request->invnr)->first();
  $items->gname = $request->gname;
  $items->notes = $request->notes; 
  $items->save();
  $items2 = InvItems::where('invnr',$request->invnr)->first();
  $items2->gname = $request->gname;
  $items2->save();
  $sucMsg = array(
      'message' => 'Erfolgreich bearbeitet',
      'alert-type' => 'success'
  );
  return redirect()->back()->with($sucMsg);

}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $computer = InvItems::where('gart_id','2')->orwhere('gart_id','3')->count();
        $server = InvItems::where('gart_id','1')->count();
        $tablet = InvItems::where('gart_id','4')->count();
        $printer = InvItems::where('gart_id','5')->count();
        $monitor = InvItems::where('gart_id','6')->count();
        $switch = InvItems::where('gart_id','7')->orwhere('gart_id','8')->orwhere('gart_id','9')->count();
        $router = InvItems::where('gart_id','10')->orwhere('gart_id','11')->count();
        $nas = InvItems::where('gart_id','12')->count();
        $projector = InvItems::where('gart_id','13')->count();
        $tkanlage = InvItems::where('gart_id','14')->count();
        $telefon = InvItems::where('gart_id','15')->orwhere('gart_id','16')->count();
        $scanner = InvItems::where('gart_id','18')->count();
        $ausgemusterd = InvAbItem::whereNotNull('ausdat')->get();
        $switchTable = InvItems::with('invroom.location.place')->with('garts')->where('gart_id','7')->orwhere('gart_id','8')->orwhere('gart_id','9')->get();
        $movements = InvMoveItem::orderBy('id','desc')->paginate(10);
        $unordereds = UnorderedComputer::All();
        
        return view('inventory.index',compact('computer','server','tablet','printer','monitor','switch','router','nas','projector','tkanlage','telefon','scanner','ausgemusterd','switchTable','movements','unordereds'));
    }

/*************************************************************{{ Machine list  }}******************************************************************************
     * Search Method listen
     */
    public function machinelist() 
    {
      $machines = InvItems::all();
      return view ('inventory.computerlist',compact('machines'));
    }

    public function machinelistAll() 
    {
      $machines = InvAbItem::whereNotNull('ausdat')->get();
      return view ('inventory.computerlistAll',compact('machines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
			$invnr = InvLastNumber::with('location')->with('room')->orderBy('last_inv_num','desc')->get()->unique('location_id')->toArray();
			$garts = Gart::get()->toArray();
			$places = Place::pluck('id','pnname')->toArray();
			return ['invnr'=>$invnr,'garts'=>$garts,'places'=>$places];
    }
    /**
     * Show the form for creating a new resource (Manuell).
     */
    public function create_man()
    {
			$places = Place::pluck('id','pnname')->toArray();
			$locations = Location::with('invrooms')->get()->toArray();
			$garts = Gart::get()->toArray();
			return ['locations'=>$locations,'garts'=>$garts,'places'=>$places];
    }
    /*************************************************************{{ Listen }}******************************************************************************
     * Search Method listen
     */
    public function listen(Request $request)
    {
			$places = Place::pluck('id','pnname')->toArray();
			$locations = Location::with('invrooms')->get()->toArray();
			return ['locations'=>$locations,'places'=>$places];
    }

    
    public function items_in_room_listen(Request $request)
    {
			$roomInventur = InvItems::with('invroom.location.place')->with('garts')->where('room_id',$request->room_id)
																->whereHas('invroom', function ($query) use ($request) {
																	return $query->where('location_id', '=', $request->location_id);
																	})->get()->toArray();
			return $roomInventur;
    }
       /*************************************************************{{ Inventur blade }}******************************************************************************
     * Search Method Inventur
     */
      public function inventoryblade(){
        return view('inventory.inventoryblade');
      }

    /*************************************************************{{ Inventur }}******************************************************************************
     * Search Method Inventur
     */
    public function inventur(Request $request)
    {
			$places = Place::pluck('id','pnname')->toArray();
			$locations = Location::with('invrooms')->get()->toArray();
			return ['locations'=>$locations,'places'=>$places];
    }
    public function roomInventur(Request $request)
    {
			$roomInventur = InvItems::with('invroom.location.place')->where('room_id',$request->room_id)
																->whereHas('invroom', function ($query) use ($request) {
																return $query->where('location_id', '=', $request->location_id);
																})->get()->toArray();
			return $roomInventur;
    }

    public function getinvnr ($invnr)
    {
      return InvItems::with('invroom.location.place')->where('invnr',$invnr)->first();
    }
    public function inventurStoreFinal(Request $request)
    {
        // foreach($request->itemList as $item){
				// if($item['zuordnen'] == '1') {
				// $move = New InvMoveItem;
				// $move -> gname = $item['gname'];
				// $move -> room_id = $item['room_id_new'];
				// $move->save();

				// $move = InvItems::Where('gname',$item['gname'])->first();
				// $move->room_id = $item['room_id_new'];
				// $move->save();
				// 	}
        // }

        return $request->itemList;
    }

        public function printinventur ()
    {
        $val_request = json_decode(request('val'), TRUE);
        $filteredArray = array_filter($val_request, function($value){
            return ($value['zuordnen'] == "0" || $value['zuordnen'] == "1" || is_null($value['zuordnen']));
						});
        return view ('inventory.print_inventur',['val'=>$filteredArray]);
    }

    public function autoChangeLocation(Request $request)
    {
      if($request->gart_id == '2' || $request->gart_id == '3'){
        UnorderedComputer::where('gname',$request->gname)->delete();
        $new_ou = InvRoom::where('id',$request->room_id_new)->first(); 
        $importRoom = [[$request->gname,$new_ou->ad_ou]];
        try {
          $importRoom = Excel::toArray(new RoomImport, 'bewegungRaum.csv','room');
          $importRoom = $importRoom[0];
          $importRoom[] = [$request->gname,$request->ad_ou];
        }
        catch (\Exception $e) {
          ;
        }
        Excel::store(new RoomExport($importRoom), 'bewegungRaum.csv','room');

        $user = Auth()->user();
        $move = New InvMoveItem;
        $move -> gname = $request->gname;
        $move -> room_id = $request->room_id_new;
        $move -> room_id_old = $request->room_id_old;
        $move -> ad_ou = $new_ou->ad_ou;
        $move -> ad_ou_old = $request->ad_ou;
        $move->who = $user->username;
        $move->save();
      }

      $move = InvItems::Where('gname',$request->gname)->first();
      $move->room_id = $request->room_id_new;
      $move->save();
      return $move->room_id;
    }
    public function sendUnorderedComputers(Request $request)
    {
    $invnr = InvItems::with('invroom')->where('invnr',$request->svg_id)->first();
    $item = new UnorderedComputer;
    $item -> gname = $invnr -> gname;
    $item -> ad_ou = $invnr -> invroom->ad_ou;
    $item -> room_id = $invnr -> invroom->id;
    $item -> who = Auth()->user()->username;
    $item -> save();
    }


    /*************************************************************{{ Ausmustern }}******************************************************************************
     * Search Method Ausmustern
     */
    public function search(Request $request)
    {
			$search_text = strtoupper($_GET['search']);
			$items = InvAbItem::with('garts')->where('invnr',$search_text)->orWhere('gname',strtoupper($search_text))->first();
			$room = InvItems::with('invroom.location')->where('invnr',$items->invnr)->first();
			$amgs = Amg::all();
			return ['items'=>$items,'room'=>$room,'amgs'=>$amgs];
    }
    /**
     * Searchcheck Method Ausmustern
     */
    public function searchCheck(Request $request)
    {
			$data = $request->all();
			$check = InvAbItem::where('invnr',$data['search'])->orwhere('gname',$data['search'])->first();
			if ($check && $data['search']!="") {
					echo "true";
			}else{
					echo "false";
			}
    }
    /**
     *  Store Method Ausmustern
     */
    public function invalid(Request $request)
    {
			$items = InvAbItem::where('invnr',$request->invnr)->with('garts')->first();
			$items->notes = $request->notes;
			$items->amg_id = $request->grund;
			$items->ausdat = date('Y-m-d');
			$items->save();

			$delItem = InvItems::where('invnr',$request->invnr)->with('invroom')->first();
			$room = $delItem->invroom->rname;
			$delItem->delete();

			$sucMsg = array(
					'message' => 'Erfolgreich bearbeitet',
					'alert-type' => 'success'
			);

			return view('inventory.print_invalid',compact('items','room'))->with($sucMsg);
    }

     /**********************************************************{{ Edit }}******************************************************************************
     * Search Method Edit
     */
    public function search_edit(Request $request)
    {
			$search_text = strtoupper($_GET['search_edit']);
			$items = InvAbItem::with('garts')->Where(function ($query) use ($search_text) {
																							$query->whereNull('ausdat')->where('invnr',$search_text);
																							})->
																							orWhere(function ($query) use ($search_text) {
																									$query->whereNull('ausdat')->where('gname',strtoupper($search_text));
																							})->first();
			$room = InvItems::with('invroom.location')->where('invnr',$items->invnr)->first();
			return ['items'=>$items,'room'=>$room];
    }
    /**
     * 
     * 
     * Search Method Edit Search_Check
     */
    public function searchCheckEdit(Request $request)
    {
			$data = $request->all();
			$check = InvAbItem::where('invnr',$data['search_edit'])->orwhere('gname',$data['search_edit'])->first();
			$check = InvAbItem::Where(function ($query) use ($data) {
																$query->whereNull('ausdat')->where('invnr',strtoupper($data['search_edit']));
																})->
																orWhere(function ($query) use ($data) {
																		$query->whereNull('ausdat')->where('gname',strtoupper($data['search_edit']));
																})->first();
			if ($check && $data['search_edit']!="") {
					echo "true";
			}else{
					echo "false";
			}
		}

    /**
     * Method Edit update
     */
    public function update(Request $request)
    {
			$items = InvAbItem::where('invnr',$request->invnr)->first();
			$items->notes = $request->notes;
			$items->save();
			$sucMsg = array(
					'message' => 'Erfolgreich bearbeitet',
					'alert-type' => 'success'
			);
			return redirect()->back()->with($sucMsg);
    }
    /**********************************************************{{ MOVE }}******************************************************************************
     * Search Method Move
     */
    public function search_move(Request $request)
    {
			$search_text = strtoupper($_GET['search_move']);
			$items = InvItems::with('invroom.location')->Where('gname',strtoupper($search_text))->first();
			$locations = Location::with('invrooms')->get()->toArray();
			$places = Place::pluck('id','pnname')->toArray();
			return ['items'=>$items,'locations'=>$locations,'places'=>$places];
    }

    public function isClassRoom (Request $request)
    {
      $classRoom = InvRoom::where('id',$request->checkClass)->whereNotNull('isClassRoom')->first();
      return ['classRoom' => $classRoom];
    }
    public function searchCheckMove(Request $request)
    {
			$data = $request->all();
			$check = InvItems::where('gname',$data['search_move'])->first();
			if ($check && $data['search_move']!="") {
					echo "true";
			}else{
					echo "false";
			}
    }
    public function movestore(Request $request)
    {
      $user = Auth()->user();
			$move = New InvMoveItem;
      $move -> gname = $request->gname_move;
      $move -> room_id_old = $request->old_room_id_move;
      $move -> room_id = $request->room_id_move;
      $ou = InvRoom::where('id',$request->room_id_move)->first();
      $move->ad_ou = $ou->ad_ou;
      $ou_old = InvRoom::where('id',$request->old_room_id_move)->first();
      $move->ad_ou_old = $ou_old->ad_ou;
      $move->who = $user->username;
      $move->save();

      $importRoom = [[$request->gname_move,$ou->ad_ou]];
      try {
        $importRoom = Excel::toArray(new RoomImport, 'bewegungRaum.csv','room');   //? Location Room defined in config/filesystems.php -> disks
        $importRoom = $importRoom[0];
        $importRoom[] = [$request->gname_move,$ou->ad_ou];
      }
      catch (\Exception $e) {
        ;
      }
      Excel::store(new RoomExport($importRoom), 'bewegungRaum.csv','room');   //? Location Room defined in config/filesystems.php -> disks

      if ($move = UnorderedComputer::where('gname',$request->gname_move)->first()) {   //? Delete Moved computer from Unordered List if exists.
        $move->delete();
      }



			$move = InvItems::Where('gname',$request->gname_move)->first();
			$move -> room_id = $request->room_id_move;
      $move->save();

      $move = InvAbItem::Where('gname',$request->gname_move)->first();
			$move -> location_id = $request->location_id_move;
      $move->save();

        $infoMsg = array(
          'message' => 'Die Änderung kann bis zu 4 Stunden dauern',
          'alert-type' => 'info'
        );

			return redirect()->back()->with($infoMsg);
    }

    public function movelogindex()
    {
    $movements = InvMoveItem::orderBy('id','desc')->get();
    return view('inventory.logs.bewegungLogs',compact('movements'));
    
    }



    public function moveToTeil(Request $request)
    {
      $classRoom = InvSubRooms::where('room_id',$request->checkClass)->where('Doz_Or_Teil','Teil')->first();
      return ['classRoom' => $classRoom];
			$move = New InvMoveItem;
      $move -> gname = $request->gname_move;
      $move->ad_ou = $request->ad_ou;
      $move->save();

      $importRoom = [[$request->gname_move,$request->ad_ou]];
      try {
        $importRoom = Excel::toArray(new RoomImport, 'room/bewegungRaum.csv','local');
        $importRoom = $importRoom[0];
        $importRoom[] = [$request->gname_move,$request->ad_ou];
      }
      catch (\Exception $e) {
        ;
      }
      Excel::store(new RoomExport($importRoom), 'room/bewegungRaum.csv','local');


			$move = InvItems::Where('gname',$request->gname_move)->first();
			$move -> room_id = $request->room_id;
      $move->save();

      $move = InvAbItem::Where('gname',$request->gname_move)->first();
			$move -> location_id = $request->location_id;
      $move->save();

        $infoMsg = array(
          'message' => 'Die Änderung kann bis zu 4 Stunden dauern',
          'alert-type' => 'info'
        );

			return redirect()->back()->with($infoMsg);
    }

/**********************************************************{{ Print label Methoad }}******************************************************************************/

    public function printlabel($printinvnr, $anzahl)
    {
			$explode = explode('-',$printinvnr);
			$anzahlnew = InvLastNumber::where('last_inv_num',($explode[1])-1)->first();
			$anzahlnew->last_inv_num += $anzahl;
			$anzahlnew->save();
			return view ('inventory.print',compact('explode','anzahl'));
    }

/**********************************************************{{ Print Missing label Methoad }}****************************************************************/

    public function missing_search()
    {
      $gerate = InvAbItem::all();
      return ['gerate'=>$gerate];
    }

    public function printmissinglabel($printinvnr)
    {
			$explode = explode('-',$printinvnr);
			return view ('inventory.print_missing',compact('explode'));
    }

/************************************************************** {{ Upload PDF }} ********************************************************************************/
    /*
    * upload Pdf and store
    */
    public function upload_pdf(Request $request)
    {
    	$pdf = $request->file('file');
			$pdfName = time() . '.' . $pdf->extension();
			$pdf->move(public_path('/inventar/rechnungen/'.date('Y')), $pdfName);
			return date('Y').'/'.$pdfName;
    }

    function fetch_pdf()
    {
     $pdfs = \File::allFiles(public_path('/inventar/rechnungen/'.date('Y')));
     $output = '<div class="row">';
			foreach($pdfs as $pdf)
			{
				$output .= '<div class="col-md-2">
									<img src="'.asset('inventar/rechnungen/pdfIcon.png').'" class="img-thumbnail" width="150" height="150"/>
									<button type="button" class="btn btn-link remove_pdf" id="'.$pdf->getFilename().'">Remove</button>
							</div>';
			}
			$output .= '</div>';
     echo $output;
    }

    function delete_pdf(Request $request)
    {
     if($request->get('name'))
     {
      \File::delete(public_path('/inventar/rechnungen/'.date('Y').'/' . $request->get('name')));
     }
    }

    /**********************************************************{{ Upload PDF Man}}******************************************************************************
     * upload Pdf and store Man
     */
    public function upload_pdf_man(Request $request)
    {

    $pdf = $request->file('file');

     $pdfName = time() . '.' . 'pdf';
     $pdf->move(public_path('/inventar/rechnungen/'.date('Y')), $pdfName);
     return date('Y').'/'.$pdfName;

    }

    function fetch_pdf_man()
    {
     $pdfs = \File::allFiles(public_path('/inventar/rechnungen/'.date('Y')));
     $output = '<div class="row">';
			foreach($pdfs as $pdf) {
				$output .= '<div class="col-md-2">
									<img src="'.asset('inventar/rechnungen/pdfIcon.png').'" class="img-thumbnail" width="150" height="150"/>
									<button type="button" class="btn btn-link remove_pdf_man" id="'.$pdf->getFilename().'">Remove</button>
							</div>';
			}
     $output .= '</div>';
     echo $output;
    }

    function delete_pdf_man(Request $request)
    {
     if($request->get('name'))
     {
      \File::delete(public_path('/inventar/rechnungen/'.date('Y').'/' . $request->get('name')));
     }
    }

    /**
     * Item Change Method
     */
    public function itemchange()
    {
        return view('inventory.change');
    }

    /**
     * Auto Creation invnr (MAIN)
     */
    public function store(Request $request)
    {
			$item = New InvAbItem;
			$item -> invnr = $request-> invnr;
			$item -> andat = $request-> andat;
			$item -> location_id = $request-> location_id;
			$item -> kp = str_replace(',','.',$request -> kp);
			$item -> gart_id = $request-> gart_id;
			$item -> gname = $request-> gname;
			$item -> gtyp = $request-> gtyp;
			$item -> sn = $request-> sn;
			$item -> notes = $request-> notes;
			$item -> path_to_rg = $request-> path_to_rg;
			$item->save();

			$item = InvItems::Where('invnr',$request->invnr)->first();
			$item->room_id = $request->room_id;
			$item->save();

			$item = New InvLastNumber;
			$split = explode('-',$request->invnr);
			$item->location_id = $split[0];
			$item->last_inv_num = $split[1];
			$item -> save();

			$sucMsg = array(
					'message' => 'Erfolgreich hinzugefügt ',
					'alert-type' => 'success'
			);
			return redirect()->back()->with($sucMsg);
    }
    /**
     * Manuell Creation invnr
     */
    public function storeMan(Request $request)
    {
			$item = New InvAbItem;
			$invnr = $request->location_id.'-'.$request->invnr.'-IT';
			$item -> invnr = $invnr;
			$item -> andat = $request-> andat;
			$item -> location_id = $request-> location_id;
			$item -> kp = str_replace(',','.',$request -> kp);
			$item -> gart_id = $request-> gart_id;
			$item -> gname = $request-> gname;
			$item -> gtyp = $request-> gtyp;
			$item -> sn = $request-> sn;
			$item -> notes = $request-> notes;
			$item -> path_to_rg = $request-> path_to_rg;
			$item->save();

			$invitems = InvItems::Where('invnr',$invnr)->first();
			$invitems->room_id = $request->room_id;
			$invitems->save();

			$item = New InvLastNumber;
			$item->location_id = $request->location_id;
			$item->last_inv_num = $request->invnr;
			$item -> save();

			$sucMsg = array(
					'message' => 'Erfolgreich hinzugefügt ',
					'alert-type' => 'success'
			);
			return redirect()->back()->with($sucMsg);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Inv_ab_item  $inv_ab_item
     * @return \Illuminate\Http\Response
     */

    public function addressbook() {

      $user = Auth()->user();
      if(auth()->user()->hasRole('Super_Admin')){
        $users = User::with('telephones')->get();
      }
      else {
      $participants = User::where('location',$user->ort)->get();
      }
        return view('settings.addressbook.addressbook',compact('users'));
    }


    public function addressbook_edit($id)
    {
      $user = User::find($id);
      $telephones = InvItems::where('gart_id','15')->pluck('gname','id')->all();
      $userTelephone = $user->telephones->pluck('name','name')->all();
      return view('settings.addressbook.addressbook_edit',compact('user','telephones','userTelephone'));
    }

    public function addressbook_update(Request $request, $id)
      {
      $this->validate($request, [
      'telephones' => 'required'
      ]);
      $input = $request->all();
      $user = User::find($id);
      $user->update($input);
      DB::table('inv_item_user')->where('user_id',$id)->delete();
      $user->telephones()->attach($request->telephones);

      $sucMsg = array(
        'message' => 'Erfolgreich bearbeitet',
        'alert-type' => 'success'
      );

      return redirect()->back()->with($sucMsg);
      }


    public function show(Invabitem $invabitem)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Inv_ab_item  $inv_ab_item
     * @return \Illuminate\Http\Response
     */
    public function edit(Invabitem $invabitem)
    {
        return view('inventory.edit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Inv_ab_item  $inv_ab_item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invabitem $inv_ab_item)
    {
        //
    }
}
