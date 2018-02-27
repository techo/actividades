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


Route::get('/', 'ActividadesController@index');
Route::get('/poc', function(){
    return view('actividades.index');
});

Route::get('/ajax/actividades', 'ajax\ActividadesController@index');
Route::get('/ajax/actividades/{id}', 'ajax\ActividadesController@show');