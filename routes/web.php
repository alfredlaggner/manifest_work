<?php

	/*
	|--------------------------------------------------------------------------
	| Web Routes
	|--------------------------------------------------------------------------
	|
	| Here is where you can register web routes for your application. These
	| routes are loaded by the RouteServiceProvider within a group which
	| contains the "web" middleware group. Now create something great!
	|
	*/

	Route::get('/', array('as' => 'make_manifest', 'uses' => 'FileController@Start'));
	Route::post('make_manifests', array('as' => 'make_manifests', 'uses' => 'FileController@makeManifests'));
	Route::get('import-customers', array('as' => 'import-customers', 'uses' => 'FileController@importCustomersIntoDB'));
	Route::get('import-products', array('as' => 'import-products', 'uses' => 'FileController@importProductsIntoDB'));
	Route::get('import-invoice', array('as' => 'import-invoice', 'uses' => 'FileController@importInvoiceIntoDB'));
	Route::get('import-units', array('as' => 'import-units', 'uses' => 'FileController@importUnitsIntoDB'));
	Route::get('import-contacts', array('as' => 'import-contacts', 'uses' => 'FileController@importContactsIntoDB'));

	Route::get('mail', array('as' => 'mail', 'uses' => 'FileController@mailtest'));

	Route::get('invoice', array('as' => 'invoice', 'uses' => 'FileController@testInvoice'));

	Route::resource('vehicles', 'VehicleController');
	Route::resource('drivers', 'DriverController');
	Route::resource('businesses', 'BusinessController');

	Route::get('detail-edit/{saleinvoice}/{total}/{log_id}', array('as' => 'detail-edit','uses' => 'SaleInvoiceController@edit'));
	Route::resource('saleinvoices', 'SaleInvoiceController');

	Route::get('drivernotes/{log_id}/{sale_order_id}', 'DriverNoteController@updates')->name('drivernotes.update');
	Route::resource('drivernotes', 'DriverNoteController');

	Route::get('driver-edit', array('as' => 'driver-edit', 'uses' => 'DriverController@index'));
	Route::get('vehicle-edit', array('as' => 'vehicle-edit', 'uses' => 'VehicleController@index'));
	Route::get('notes-edit', array('as' => 'vehicle-edit', 'uses' => 'NotesController@index'));

	Route::get('additional', array('as' => 'additional', 'uses' => 'FileController@additional'));
	Route::get('go-home', array('as' => 'go-home', 'uses' => 'DriverLogController@index'));
	Route::get('go-viewer', array('as' => 'go-viewer', 'uses' => 'DriverLogController@viewer'));
	Route::get('delivery_done/{log_id}', array('as' => 'delivery_done', 'uses' => 'DriverLogController@delivery_done'));


	Route::get('odoo', array('as' => 'odoo', 'uses' => 'OdooController@index'));


	Route::get('log', array('as' => 'log', 'uses' => 'DriverLogController@index'));
	Route::get('viewer', array('as' => 'viewer', 'uses' => 'DriverLogController@viewer'));
	Route::resource('driverlogs', 'DriverLogController');
	Route::resource('notes', 'NotesController');
	Route::resource('line_notes', 'LineNoteController');

	Auth::routes();

	Route::get('/home', 'HomeController@index')->name('home');

	/*laratables*/
	Route::get('/get-simple-datatables-data', 'DataTableController@getSimpleDatatablesData')->name('simple_datatables_users_data');

	Route::get('/get-custom-column-datatables-data', 'DataTableController@getCustomColumnDatatablesData')->name('custom_column_datatables_users_data');

	Route::get('/get-relationship-column-datatables-data', 'DataTableController@getRelationshipColumnDatatablesData')->name('relationship_column_datatables_users_data');

	Route::get('/get-extra-data-datatables-attributes-data', 'DataTableController@getExtraDataDatatablesAttributesData')->name('get_extra_data_datatables_attributes_data');
	Route::get('/download1/{id}', 'HomeController@download1')->name('download1');

	Route::get('edit_action/{sale_order_id}', 'DriverLogController@edit_action')->name('edit_action');
	Route::get('create_notes/{log_id}/{sale_order_id}/{total}', 'DriverLogController@create_notes')->name('create_notes');
	Route::get('display_notes/{log_id}/{sale_order_id}', 'DriverLogController@display_notes')->name('display_notes');
	Route::get('print_so_report/{log_id}/{sale_order_id}/{total}', 'DriverLogController@print_so_report')->name('print_so_report');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
