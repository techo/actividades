<?php
use App\Actividad;

Route::get('/', 'HomeController@index')->name('home');
Route::get('/actividades', 'ActividadesController@index');

Route::get('/poc', function(){
    return view('actividades.index');
});
Route::get('/actividades/{id}', function($id){
	$actividad = Actividad::find($id);
    return view('actividades.show')->with('actividad', $actividad);
});

Route::get('/ajax/categorias/{id}', 'ajax\CategoriasController@show');
Route::get('/ajax/provincias/{id}', 'ajax\ProvinciasController@show');
Route::get('/ajax/actividades', 'ajax\ActividadesController@index');
Route::get('/ajax/actividades/{id}', 'ajax\ActividadesController@show');
Auth::routes();

Route::get('autenticado', function() {
    return (\Illuminate\Support\Facades\Auth::check()) ? 'si' : 'no';
});