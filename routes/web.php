<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\InvAbItemController;
// use App\Http\Controllers\PracticeCompanyController;

Auth::routes();

Route::get('city/{city}/tickets/pdf', 'TicketController@generateCityTicketsPdf')->name('city.tickets.pdf');

//! Korso aufgaben //
Route::resource('onlinemarketing_items', 'OnlinemarketingItemController');
Route::resource('zertifizierung_items', 'ZertifizierungItemController');
Route::get('korso', 'KorsoController@index')->name('korso_index');  // index
Route::get('/korso/{id}', 'KorsoController@show')->name('korso.show');  //show
// Route::delete('/korso/delete/{id}', 'KorsoController@destroy')->name('korso.delete'); // delete
Route::delete('/korso/{korso}', 'KorsoController@destroy')->name('korso.delete'); // delete
Route::post('/korso/assign', 'KorsoController@assignUser')->name('korso.assign'); // assignto ajax
Route::get('printmarketing', 'KorsoController@printmarketing')->name('printmarketing');
Route::get('onlinemarketing', 'KorsoController@onlinemarketing')->name('onlinemarketing');
Route::get('zertifizierung', 'KorsoController@zertifizierung')->name('zertifizierung');
Route::post('form_store_korso', 'Korsocontroller@form_store_korso')->name('form_store_korso');
Route::get('/dashboard/filter-tickets', 'KorsoController@filterTickets')->name('dashboard.filterTickets');
Route::post('/korso/{korso}/done', 'KorsoController@markAsDone')->name('korso.markDone');  //softDelete ticket
Route::post('/korso/{korso}/restore', 'KorsoController@restore')->name('korso.restore');  //restore softDeleted ticket
Route::get('/korso/{id}/details', 'KorsoController@getTicketDetails'); // Sideslide
Route::get('/user-management', 'KorsoController@userManagement')->name('user.management');
Route::post('/assign-role', 'KorsoController@assignRole')->name('assign.role');
Route::post('/remove-role', 'KorsoController@removeRole')->name('remove.role');
Route::post('/korso/update-status/{id}', 'KorsoController@updateStatus')->name('korso.updateStatus');
Route::post('/korso/force_delete/{id}', 'KorsoController@fullDestroy')->name('korso.forceDelete');
Route::post('korso/{id}/upload-attachment', 'KorsoController@uploadAttachment')
  ->name('korso.attachment.upload'); // korso ma upload 
Route::delete('/korso/{korso}/attachment/{attachment}', 'KorsoController@deleteAttachment')
  ->name('korso.attachment.delete');
Route::get('/korso/{id}/download-pdf', 'KorsoController@downloadPdf')->name('korso.download.pdf'); //download pdf
Route::get('korso/printmarketing/pdf','KorsoController@exportPrintmarketingPdf')->name('printmarketing.pdf'); // download printmanagement

//! Onlinemanagement
Route::get('printmarketing-management', 'KorsoController@printmarketingManagement')
  ->name('printmarketing.management');
Route::post('korso-item/{korsoItem}/toggle-ordered', 'KorsoController@toggleOrdered')
  ->name('korso-item.toggleOrdered');

//! Korso Internal Comments //
Route::post('/korso/comments/store', 'KorsoInternalCommentController@store')->name('korso.comments.store');
Route::patch('/korso/comments/{internalComment}', 'KorsoInternalCommentController@update')
  ->name('korso.comments.update');
Route::patch('/korso/comments/{internalComment}/delete', 'KorsoInternalCommentController@softDelete')
  ->name('korso.comments.softDelete');
Route::patch('/korso/comments/{internalComment}/restore', 'KorsoInternalCommentController@restore')
  ->name('korso.comments.restore');

//! Sekretariat Groupe
Route::resource('sek-groups', 'SekGroupController');
Route::get('sek-groups/{group}/members', 'SekGroupController@editMembers')->name('sek-groups.members.edit');
Route::post('sek-groups/{group}/members', 'SekGroupController@updateMembers')->name('sek-groups.members.update');

// Add members
Route::post('sek-groups/{group}/members/add','SekGroupController@addMembers')->name('sek-groups.members.add');
// Remove a single member
Route::post('sek-groups/{group}/members/remove','SekGroupController@removeMember')->name('sek-groups.members.remove');


//! Handwerk aufgaben //
//! PDF download
Route::get('handwerk/{city}/open-tickets-pdf', 'HandwerkController@openTicketsPDF')->name('handwerk.city.openTicketsPDF');

Route::get('handwerk', 'HandwerkController@index')->name('handwerk_index');  // index
Route::get('/handwerk/{myHandwerkTicket}', 'HandwerkController@show')->name('handwerk_show');  // show

Route::get('einrichtungsgegenstände', 'Handwerkcontroller@einrichtungsgegenstände')->name('einrichtungsgegenstände'); //
Route::get('elektro', 'Handwerkcontroller@elektro')->name('elektro');
Route::get('neustandort', 'Handwerkcontroller@neustandort')->name('neustandort');
// reparatur
Route::get('reparatur_elektro', 'Handwerkcontroller@reparatur_elektro')->name('reparatur_elektro');
Route::get('reparatur_mobiliar', 'Handwerkcontroller@reparatur_mobiliar')->name('reparatur_mobiliar');
// modifikation
Route::get('modifikation', 'Handwerkcontroller@modifikation')->name('modifikation');

Route::post('form_store_handwerk', 'Handwerkcontroller@form_store_handwerk')->name('form_store_handwerk');

Route::get('/my-handwerks-tickets', 'HandwerkController@myHandwerks')->name('my_handwerks');

// Delete  / restore
Route::post('/handwerk.delete/{myhandwerk}', 'HandwerkController@destroy')->name('handwerk.delete');
Route::post('/handwerk.restore/{myhandwerk}', 'HandwerkController@restore')->name('handwerk.restore');
//! ajax delete
Route::post('/handwerk/ajax-destroy/{id}', 'HandwerkController@ajaxDestroy')->name('handwerk.ajaxDestroy');


// Handwerk History
Route::get('/usertHandwerkicketshistory', 'HandwerkController@userhandwerkticketshistory')->name('handwerk.userhandwerkticketsdone');

// Assigned to 
Route::post('/handwerk/assignTo', 'HandwerkController@assignedTo')->name('handwerk.assignedTo');

// Handwerk Admin Notice
Route::post('/handwerk/admin_notes', 'HandwerkController@admin_notes')->name('handwerk.admin_notes');

// city route
Route::get('/handwerker/{city}', 'HandwerkController@showCity')->name('handwerk.city');
Route::post('/handwerker/{city}/todos', 'HandwerkController@storeTodo')->name('handwerk.city.todos.store');
Route::get('/handwerker/{city}/todos/{todo}/edit', 'HandwerkTodoController@edit')->name('handwerk.city.todos.edit');
Route::put('/handwerker/{city}/todos/{id}', 'HandwerkTodoController@updateTodo')->name('handwerk.city.todos.update');
Route::Delete('/handwerker/{city}/todos/{id}', 'HandwerkTodoController@destroy')->name('handwerk.city.todos.destroy');


// location ajax
Route::get('/room/list/{city?}', 'HandwerkController@room_list')->name('room_list');


// Route::patch('/handwerker/{city}/todos/{todo}/done', 'HandwerkTodoController@done')->name('handwerk.city.todos.done');

Route::get('/handwerk/{id}', ['HandwerkController@show'])->name('handwerk_show');
// room list ajax request
Route::get('/room/lists', 'HandwerkController@roomsList')->name('rooms.lists');




// Route::get('/', 'TicketController@index')->name('home');
Route::get('/', 'TicketController@landing')->name('home');
Route::get('/dashboard', 'LicenseController@index')->name('dashboard');
Route::get('/korso-dashboard', 'KorsoController@dashboard')->name('korso.dashboard');


//! video //
Route::get('/video', 'TicketController@video')->name('video');
Route::get('/video_index', 'TicketController@video_index')->name('video_index');

Route::group(['middleware' => ['auth']], function () {
  Route::resource('roles', 'RoleController');
  Route::resource('users', 'UserController'); //add to User Modal protected $guard_name = 'web';
  Route::resource('permissions', 'PermissionController');
});


// one time query from temp to inv_ab_item table
Route::get('/sync', 'TempInvAbItemController@index')->name('sync');
// query last InvNumber
Route::get('/auto', 'InvLastNumberController@index')->name('auto');
Route::get('/search_missing_label', 'InvAbItemController@missing_search')->name('search_missing');

//*************************************************   Inventory Index  *********************************************************************/
Route::get('/inventory', 'InvAbItemController@index')->name('inventory');

//***********************  machinelist Items   *********************************/
Route::get('/machinelist', 'InvAbItemController@machinelist')->name('machine_list');
Route::get('/machinelistall', 'InvAbItemController@machinelistall')->name('machine_list_all');

//***********************  Create Items   *********************************/
// create Address and room lists
Route::get('/item/create', 'InvAbItemController@create')->name('item.create');
Route::post('/item', 'InvAbItemController@store')->name('item.store');
//*********  PDF Upload   ***********/
Route::post('dropzone/upload_pdf', 'InvAbItemController@upload_pdf')->name('dropzone.upload_pdf');
Route::get('dropzone/fetch_pdf', 'InvAbItemController@fetch_pdf')->name('dropzone.fetch_pdf');
Route::get('dropzone/delete_pdf', 'InvAbItemController@delete_pdf')->name('dropzone.delete_pdf');

///***********************  Create Items Manuall   *************************/
Route::get('/item/create_man', 'InvAbItemController@create_man')->name('item.create_man');
Route::post('/item_man', 'InvAbItemController@storeMan')->name('item.storeMan');
//*******************  PDF Upload Man  ********************/
Route::post('dropzone/upload_pdf_man', 'InvAbItemController@upload_pdf_man')->name('dropzone.upload_pdf_man');
Route::get('dropzone/fetch_pdf_man', 'InvAbItemController@fetch_pdf_man')->name('dropzone.fetch_pdf_man');
Route::get('dropzone/delete_pdf_man', 'InvAbItemController@delete_pdf_man')->name('dropzone.delete_pdf_man');

//*********************  Edit Items   **************************************/
Route::get('/search_edit', 'InvAbItemController@search_edit')->name('search_edit');
Route::post('search_check_edit', 'InvAbItemController@searchCheckEdit')->name('search_check_edit');
Route::patch('/item/update', 'InvAbItemController@update')->name('item.update');

//*********************  Rename Items   **************************************/
Route::get('/search_rename', 'InvAbItemController@search_rename')->name('search_rename');
Route::post('search_check_rename', 'InvAbItemController@searchCheckrename')->name('search_check_rename');
Route::patch('/item/updaterename', 'InvAbItemController@updateRename')->name('item.updaterename');


//Route::post('autocompleterename','InvAbItemController@autocompleteRename')->name('autocompleteRename');

//*********************  Move Items   **************************************/
Route::get('/search_move', 'InvAbItemController@search_move')->name('search_move');
Route::post('search_check_move', 'InvAbItemController@searchCheckMove')->name('search_check_move');
Route::post('/item_move_store', 'InvAbItemController@movestore')->name('item_move_store');
Route::get('/item_move_log_index', 'InvAbItemController@movelogindex')->name('item_move_log_index');
Route::get('/isClassRoom', 'InvAbItemController@isClassRoom')->name('isClassRoom');

//*********************  Move Items   **************************************/
Route::get('/item/move', 'InvAbItemController@move')->name('item.move');
Route::post('/moveToTeil', 'InvAbItemController@moveToTeil')->name('moveToTeil'); //move to teilnehmer folder

//*********************  Invalidation Items  Ausmusterung  *****************/
Route::get('/search', 'InvAbItemController@search')->name('search');
Route::post('search_check', 'InvAbItemController@searchCheck')->name('search_check');
Route::post('/invalid', 'InvAbItemController@invalid')->name('invalid');

//*****************************  PrintLabel  ****************************************/
Route::get('/print/{printinvnr}/{anzahl}', 'InvAbItemController@printlabel')->name('printlabel');
Route::get('/printmissing/{printinvnr}', 'InvAbItemController@printmissinglabel')->name('printmissinglabel');

//*****************************  Print Listen  **************************************/
Route::get('/item/listen', 'InvAbItemController@listen')->name('item.listen');
Route::post('/room_listen', 'InvAbItemController@items_in_room_listen')->name('items_in_room_listen');

//********************************    Items Inventory  ******************************/
Route::get('/item/inventur', 'InvAbItemController@inventur')->name('item.inventur');     //Item Inventur
Route::post('/room_inventur', 'InvAbItemController@roomInventur')->name('roomInventur'); //room Inventur
Route::get('room/inventur/{invnr?}', 'InvAbItemController@getinvnr')->name('getinvnr');  //list add item in Inventur room listlist
Route::post('/inventur_store_final', 'InvAbItemController@inventurStoreFinal')->name('inventurStoreFinal'); //store
Route::post('/auto_change_location', 'InvAbItemController@autoChangeLocation')->name('autoChangeLocation'); //auto change room

Route::post('/send_unordered_computers', 'InvAbItemController@sendUnorderedComputers')->name('sendUnorderedComputers'); //send unordered computers
Route::get('/print_inventur', 'InvAbItemController@printinventur')->name('printinventur');


Route::get('/inventoryblade', 'InvAbItemController@inventoryblade')->name('inventoryblade');


//******************************************  Settings  ******************************************************/
Route::get('/settings', 'SettingController@index')->name('setting_index');
/* Add City */
Route::post('/create_city', 'PlaceController@addCity')->name('addCity');
/* Add Location City list *AJAX* */
Route::get('/settings/cityList', 'LocationController@cityList')->name('settings.cityList');
Route::get('/settings/cityAddressList', 'InvRoomController@cityAddressList')->name('settings.cityAddressList');
/* Add Location City ROOM  (Add - Save )*/
Route::post('/create_address', 'LocationController@addLocation')->name('addLocation');
Route::post('/create_room', 'InvRoomController@addRoom')->name('addRoom');

/* Role index */
Route::get('/settings/roleList', 'RoleController@index')->name('settings.roleList');
/* users index */
Route::get('/settings/usersList', 'UserController@index')->name('settings.usersList');
/* first page */
Route::get('/settings/firstpage/{id}/edit', 'SettingController@firstpage')->name('settings.firstpage');
Route::patch('/settings/firstpage/{id}', 'SettingController@firstupdate')->name('settings.firstupdate');
/* Publish News */
//******************************************  News  ******************************************************/
Route::get('/settings/publish/popup/1', 'NewsController@show')->name('popup.show');
Route::PUT('/popup_update/1', 'NewsController@update')->name('popup.update');
Route::get('/news.popup.checks', 'NewsController@news_check')->name('news.popup.check');

//******************************************  News BAR ******************************************************/
Route::get('/newsbar.check', 'NewsBarController@news_bar_check')->name('newsbar.check');
Route::get('/settings/publish/newsbar/1', 'NewsBarController@show')->name('newsbar.show');
Route::PUT('/newsbar_update/1', 'NewsBarController@update')->name('newsbar.update');

//****************************************** Standortbesuch ******************************************************/
Route::get('/settings/standortbesuch', 'StandortbesuchController@index')->name('standortbesuch');
Route::post('/settings/standortbesuch/berlin', 'StandortbesuchController@standortbesuch_berlin')->name('standortbesuch_berlin');
Route::post('/settings/standortbesuch/berlinii', 'StandortbesuchController@standortbesuch_berlinii')->name('standortbesuch_berlinii');
Route::post('/settings/standortbesuch/chemnitz', 'StandortbesuchController@standortbesuch_chemnitz')->name('standortbesuch_chemnitz');
Route::post('/settings/standortbesuch/dresden', 'StandortbesuchController@standortbesuch_dresden')->name('standortbesuch_dresden');
Route::post('/settings/standortbesuch/leipzig', 'StandortbesuchController@standortbesuch_leipzig')->name('standortbesuch_leipzig');
Route::post('/settings/standortbesuch/suhl', 'StandortbesuchController@standortbesuch_suhl')->name('standortbesuch_suhl');


//! License // 
Route::resource('licenses', 'LicenseController');


//! Adress book // 
//******************************************  Address Book  ******************************************************/
Route::get('/settings/addressbook', 'InvAbItemController@addressbook')->name('settings.addressbook');
Route::get('/settings/addressbook/{id}/edit/', 'InvAbItemController@addressbook_edit')->name('settings.addressbook_edit');
Route::patch('/settings/addressbook/addressbook_update/{id}', 'InvAbItemController@addressbook_update')->name('settings.addressbook_update');


//******************************************  Profile  ******************************************************/
Route::get('/profile', 'UserController@profile')->name('profile');

//******************************************  Matrix  ******************************************************/
Route::get('/matrix/berlin', 'Matrix\BerlinController@index')->name('matrix.berlin');

//******************************************  Contact  ******************************************************/
Route::get('/contacts', 'ContactController@index')->name('contact.index');
Route::post('/contacts/location', 'ContactController@contacts_phones_in_location')->name('contacts_phones_in_location');
Route::post('/contacts/room', 'ContactController@contacts_phones_in_room')->name('contacts_phones_in_room');
Route::post('/address', 'ContactController@dynamicAddresses')->name('address'); //generate address list from Cities list
Route::post('/rooms', 'ContactController@dynamicrooms')->name('rooms'); // generate rooms list from addresses list
Route::post('/searchbyname', 'ContactController@searchByName')->name('searchByName'); //search contact by name
Route::post('/searchbyusername', 'ContactController@searchByUsername')->name('searchByUsername'); //search contact by username


//******************************************  Ticket  ******************************************************/
Route::get('/usertickets/{city?}', 'TicketController@usertickets')->name('ticket.usertickets');
Route::get('/userticketshistory', 'TicketController@userticketshistory')->name('ticket.userticketsdone');
Route::get('/ticket.index', 'TicketController@index')->name('ticket.index');
Route::get('/landingPage', 'TicketController@landingPage')->name('ticket.landingPage');
Route::post('/problem_type', 'TicketController@problem_type')->name('problem_type');
Route::post('/dependant_forms', 'TicketController@dependant_forms')->name('forms');
Route::post('/problem_type_machine', 'TicketController@problem_type_machine')->name('problem_type_machine');
Route::post('/form_store', 'TicketController@store')->name('form_store');
Route::get('/ticket/address', 'TicketController@address')->name('ticket.address');
Route::get('/ticket/allRead', 'TicketController@allRead')->name('allRead');
Route::get('/individual/{$id}', 'TicketController@individualtickets')->name('ticket.ownticket');
Route::get('/mammach', 'TicketController@mammach')->name('ticket.mammach');

Route::get('/tickets/{userId?}', 'TicketController@userTicketsAdmins')->name('userTicketsAdmins');  //! test merge admins
Route::get('/tickets/city/{cityName}', 'TicketController@cityTickets')->name('city.tickets');


Route::patch('/notes/{note}', 'NoteController@update')->name('notes.update');
Route::post('/notes', 'NoteController@store')->name('notes.store');
Route::delete('/notes/{note}', 'NoteController@destroy')->name('notes.destroy');




Route::get('/basti', 'TicketController@basti')->name('ticket.basti');
Route::get('/ara', 'TicketController@ara')->name('ticket.ara');
Route::get('/rolf', 'TicketController@rolf')->name('ticket.rolf');
Route::get('/berlin', 'TicketController@berlin')->name('ticket.berlin');
Route::get('/chemnitz', 'TicketController@chemnitz')->name('ticket.chemnitz');
Route::get('/dresden', 'TicketController@dresden')->name('ticket.dresden');
Route::get('/leipzig', 'TicketController@leipzig')->name('ticket.leipzig');
Route::get('/suhl', 'TicketController@suhl')->name('ticket.suhl');
Route::get('/döbeln', 'TicketController@döbeln')->name('ticket.döbeln');


Route::get('/getCityTicketsDetails/{cityName}','StandortbesuchController@getCityTicketDetail')->name('city.tickets.details');



//****************************************  Ticket Computer  *************************************************/
Route::get('/ticket.computer_all', 'TicketController@computer_all')->name('computer_all');
Route::get('/ticket.software_request', 'TicketController@softwareRequest')->name('softwareRequest');
Route::get('/ticket.software_error', 'TicketController@softwareError')->name('softwareError');
Route::get('/ticket.software_install', 'TicketController@softwareInstall')->name('softwareInstall');
Route::get('/ticket.peripheral_request', 'TicketController@peripheralRequest')->name('peripheralRequest');
Route::get('/ticket.hardware_request', 'TicketController@hardwareRequest')->name('hardwareRequest');
Route::get('/ticket.pc_problems', 'TicketController@pc_problems')->name('pc_problems');
Route::get('/ticket.other', 'TicketController@other')->name('other');

//****************************************  Ticket Printer  *************************************************/
Route::get('/ticket.printer', 'TicketController@printer_in_out')->name('printer_in_out');
Route::post('/ticket.printer_search_inroom', 'TicketController@printer_in_room')->name('printer_in_room'); //! AJAX find the printer  
Route::get('/ticket.printer_all', 'TicketController@printer_all')->name('printer_all');
Route::get('/ticket.scanner', 'TicketController@scanner')->name('scanner');
Route::get('/ticket.scanner.new', 'TicketController@scannerNew')->name('scannerNew');
Route::get('/ticket.function', 'TicketController@functuality')->name('functuality');
Route::get('/ticket.errors', 'TicketController@errors')->name('errors');

//****************************************  Ticket Projectogr  *************************************************/
Route::get('/ticket.projector_problems', 'TicketController@projectorProblems')->name('projector_problems');
Route::post('/ticket.pro_search_inroom', 'TicketController@pro_in_room')->name('pro_in_room');      //! find the Projector 

//****************************************  Ticket Telephone  *************************************************/
Route::get('/ticket.telephone_all', 'TicketController@telephone_all')->name('telephone_all');
Route::get('/ticket.tel_problems', 'TicketController@tel_problems')->name('tel_problems');
Route::get('/ticket.tel_changes', 'TicketController@tel_changes')->name('tel_changes');
Route::get('/ticket.tel_changes_location', 'TicketController@tel_changes_location')->name('tel_changes_location');
Route::get('/ticket.tel_changes_name', 'TicketController@tel_changes_name')->name('tel_changes_name');
Route::get('/ticket.tel_changes_number', 'TicketController@tel_changes_number')->name('tel_changes_number');
Route::post('/ticket.tel_search_inroom', 'TicketController@tel_in_room')->name('tel_in_room');      //! find the Telephone 

//****************************************  Ticket Users  *************************************************/
Route::get('/ticket.users_all', 'TicketController@users_all')->name('users_all');
Route::get('/ticket.employee', 'TicketController@employee')->name('users_employee');
Route::get('/ticket.users_others', 'TicketController@users_others')->name('users_others');
Route::get('/ticket.users_namechange', 'TicketController@users_namechange')->name('users_namechange');
Route::get('/ticket.users_loginProblem', 'TicketController@users_loginProblem')->name('users_loginProblem');
Route::get('/ticket.participant', 'TicketController@participant')->name('users_participant');
Route::get('/ticket.forward', 'TicketController@emailForward')->name('users_email_forward');
Route::post('/ticket.participant.store', 'TicketController@store_participant')->name('store_participant'); //! store participant dynamic table 
Route::get('donwload_muster', 'TicketController@download_muster')->name('download_muster');
Route::post('/participant.username', 'TicketController@participant_username_update')->name('participant_username_update'); //! ajax update usernames in participant table
Route::post('/ticket.force_delete/{id}', 'TicketController@forceDelete')->name('ticket.forceDelete');

//****************************************  Ticket Admins  *************************************************/
Route::get('/opentickets', 'TicketController@opentickets')->name('ticket.opentickets');
Route::get('/unassignedtickets', 'TicketController@unassignedtickets')->name('ticket.unassigned');
Route::get('/tickethistory', 'TicketController@tickethistory')->name('ticket.history');
Route::get('/ticket/{myTicket}', 'TicketController@show')->name('ticket.show');
Route::post('/ticket.delete/{myTicket}', 'TicketController@destroy')->name('ticket.delete');
Route::post('/ticket.delete2/{myTicket}', 'TicketController@mitarbeitersave')->name('ticket.delete2');
Route::post('/ticket.restore/{myTicket}', 'TicketController@restore')->name('ticket.restore');
Route::post('/ticket/assignTo', 'TicketController@assignedTo')->name('ticket.assignedTo');
Route::post('/ticket/priority', 'TicketController@ticketPriority')->name('ticket.ticketPriority');
Route::post('/ticket/status', 'TicketController@ticketStatus')->name('ticket.ticketStatus');
Route::post('/ticket/admin_notes', 'TicketController@admin_notes')->name('ticket.admin_notes');
Route::post('/ticket/employee_username', 'TicketController@employee_username')->name('ticket.employee_username'); //! employee username ajax 
Route::post('/ticket/employee_password', 'TicketController@employee_password')->name('ticket.employee_password'); //! employee password ajax 
Route::post('/ticket/employee_email', 'TicketController@employee_email')->name('ticket.employee_email'); //! employee email ajax 
Route::post('/ticket/employee_ISusername', 'TicketController@employee_ISusername')->name('ticket.employee_ISusername');  //! employee ISusername ajax 

Route::post('ticket/update-remark/{city}', 'TicketController@updateRemark')->name('ticket.updateRemark');


Route::post('/ticket/on_location', 'TicketController@on_location')->name('ticket.on_location');
Route::post('/ticket/on_location_reverse', 'TicketController@on_location_reverse')->name('ticket.on_location_reverse');


//****************************************  Ticket Web  *************************************************/
Route::get('/ticket.web_all', 'TicketController@web_all')->name('web_all');
Route::get('/ticket.terminal_tn', 'TicketController@terminal_tn')->name('terminal_tn');
Route::get('/ticket.bbb', 'TicketController@bbb')->name('bbb');
Route::get('/ticket.vtiger', 'TicketController@vtiger')->name('vtiger');
Route::get('/ticket.firmenvz', 'TicketController@firmenvz')->name('firmenvz');
Route::get('/ticket.smt', 'TicketController@smt')->name('smt');

//! Participant table
Route::get('participants/test', 'ParticipantTicketTableController@index2');
Route::resource('participants', 'ParticipantTicketTableController');

//****************************************  Tasks  *************************************************/
Route::get('/tasks', 'TaskController@index')->name('tasks.index'); //!tasks

//! Project
Route::post('projects/{project}/detach-ticket/{ticket}', 'ProjectController@detachTicket')->name('projects.detach-ticket');
Route::post('projects/{project}/store-devices', 'ProjectController@storeDevices')->name('projects.store-devices');
Route::post('projects/{project}/attach-tickets', 'ProjectController@attachTickets')->name('projects.attach-tickets');
Route::resource('projects', 'ProjectController');


Route::get('projects/{project}/jobs/create', 'JobController@create')->name('job.create');
Route::post('projects/{project}/jobs', 'JobController@store')->name('job.store');



//******************************************  Termination  ******************************************************/
Route::POST('terminations.delete/{id}', 'TerminationController@destroyed')->name('termination_delete');
Route::POST('terminations/upload', 'TerminationController@storeUpload')->name('termination_upload');
Route::GET('terminations/upload_terminations', 'TerminationController@createUpload')->name('terminationCreate_upload');
Route::GET('terminations/history', 'TerminationController@history')->name('termination_history');
Route::GET('terminations/restore/{id}', 'TerminationController@restore')->name('termination.restore');

Route::resource('terminations', 'TerminationController');

//! Employees
Route::resource('employees', 'EmployeeController');


//! Reminder
Route::post('/ticket/set-reminder', 'TicketController@setReminder')->name('ticket.setReminder');
Route::get('/mark-notification-read/{id}', 'ReminderController@markAsReadAjax')->name('reminder.markReadAjax');
Route::patch('/reminders/{reminder}/toggle-remind', 'ReminderController@toggleRemind')->name('reminders.toggle-remind');
Route::resource('reminders', 'ReminderController');






Route::resource('umfrages', 'UmfrageController');
Route::get('/umfrages/getByPlace/{place}', 'UmfrageController@getByPlace');

Route::post('/umcategories/store', 'UmcategoryController@store')->name('umcategories.store');
Route::post('/umcategories/{umcategory}', 'UmcategoryController@edit')->name('umcategories.edit');
Route::patch('/umcategories/{umcategory}', 'UmcategoryController@update')->name('umcategories.update');
Route::delete('/umcategories/{umcategory}', 'UmcategoryController@destroy')->name('umcategories.destroy');


//! devices
Route::resource('devices', 'DeviceController');

//! devie status
Route::resource('device-statuses', 'DeviceStatusController');

//! PracticeController
use App\Http\Controllers\StandortbesuchController;
use App\Http\Controllers\PracticeCompanyController;
use App\Http\Controllers\KorsoInternalCommentController;

// Route::get('practice-companies/export', 'PracticeCompanyController@export')->name('practice-companies.export');
Route::get('practice-companies/city/{city}', 'PracticeCompanyController@viewByCity')->name('practice-companies.city');
Route::get('practice-companies/lex/{city}', 'PracticeCompanyController@viewByLex')->name('practice-companies.lex');


// Route for exporting practice companies
Route::get('practice-companies/export', 'PracticeCompanyController@export')->name('practice-companies.export');

// Resource route for practice companies
Route::resource('practice-companies', 'PracticeCompanyController');




//! word documents
// Document routes
Route::get('/documents', 'DocumentController@index')->name('documents.index');
Route::get('/documents/variables/{bundesland}', 'DocumentController@showVariables'); // New route
Route::post('/documents/edit', 'DocumentController@edit');
Route::post('/documents/update', 'DocumentController@update');
