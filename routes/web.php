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
use App\Http\Middleware\ApiAuthMiddleware;

//RUTAS PARA EL CONTROLADOR DE USUARIOS
Route::post('api/login', 'UserController@login');
Route::resource('api/users', 'UserController')->middleware(ApiAuthMiddleware::class);
Route::put('api/users/update/password/{id}', 'UserController@updatePassword')->middleware(ApiAuthMiddleware::class);

//RUTAS PARA EL CONTROLADOR DE DOCUMENTOS
Route::resource('api/documents', 'DocumentsController')->middleware(ApiAuthMiddleware::class);
Route::get('api/documents/search/word/{word}', 'DocumentsController@showByWord')->middleware(ApiAuthMiddleware::class);
Route::get('api/documents/search/folder/{folder}', 'DocumentsController@showByFolder')->middleware(ApiAuthMiddleware::class);
Route::get('api/documents/get-file/{filename}', 'DocumentsController@getFile')->middleware(ApiAuthMiddleware::class);
Route::post('api/documents/upload-file', 'DocumentsController@uploadFiles')->middleware(ApiAuthMiddleware::class);
Route::delete('api/documents/delete-file/{filename}', 'DocumentsController@deleteFile')->middleware(ApiAuthMiddleware::class);

//RUTAS PARA EL CONTROLADOR DEL EMAIL
Route::post('api/documents/sendemail/send', 'SendEmailController@send');

//RUTAS PARA EL CONTROLADOR DE FOLDER
Route::resource('api/folder', 'FolderController');

//RUTAS PARA EL CONTROLADOR DE INCOME RECORD
Route::post('api/income-record', 'IncomeRecordController@store');
