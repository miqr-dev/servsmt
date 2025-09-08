//****************************************  Ticket Computer  *************************************************/
Route::get('/ticket.computer_all','TicketController@computer_all')->name('computer_all'); //duplicate
Route::get('/ticket.software_request','TicketController@softwareRequest')->name('softwareRequest'); 
Route::get('/ticket.peripheral_request','TicketController@peripheralRequest')->name('peripheralRequest'); 
Route::get('/ticket.hardware_request','TicketController@hardwareRequest')->name('hardwareRequest'); 
Route::get('/ticket.pc_problems','TicketController@pc_problems')->name('pc_problems'); 
Route::get('/ticket.printer','TicketController@printer_in_out')->name('printer_in_out');  // same page in PC / Laptops and Drucker
Route::get('/ticket.other','TicketController@other')->name('other'); 
Route::post('/ticket.printer_search_inroom','TicketController@printer_in_room')->name('printer_in_room'); //! AJAX find the printer  