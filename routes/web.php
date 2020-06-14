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
// =================================================================================
// ====================Rutas para el controlador de usuarios========================
// =================================================================================
Route::post('api/login', 'UserController@login');
Route::resource('api/users', 'UserController')->middleware(ApiAuthMiddleware::class);
Route::put('api/users/update/password/{id}', 'UserController@updatePassword')->middleware(ApiAuthMiddleware::class);

// =================================================================================
// ===================Rutas para el controlador de documentos=======================
// =================================================================================
Route::resource('api/documents', 'DocumentsController');
Route::get('api/documents/search/word/{word}', 'DocumentsController@showByWord');
Route::get('api/documents/search/folder/{folder}', 'DocumentsController@showByFolder');
Route::get('api/documents/get-file/{filename}', 'DocumentsController@getFile');
Route::post('api/documents/upload-file', 'DocumentsController@uploadFiles')->middleware('api.auth');
Route::delete('api/documents/delete-file/{filename}', 'DocumentsController@deleteFile')->middleware('api.auth');

// =================================================================================
// ====================Rutas para el controlador de carpetas========================
// =================================================================================
Route::resource('api/folder', 'FolderController');
Route::get('api/folder/get-folder-by-folder-id/{folder_id}', 'FolderController@getFolderByFolderId');

//RUTAS PARA EL CONTROLADOR DE INCOME RECORD
Route::post('api/income-record', 'IncomeRecordController@store');
