<?php

use App\Http\Controllers\PeticioneController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





Route::controller(CategoriaController::class)->group(function(){
    Route::get('/categorias','index');
    Route::post('/categoria','store');
    Route::get('/categoria/{categoria}','show');
    Route::get('/categoria/{categoria}/peticiones','listPeticiones');
});


Route::controller(UserController::class)->group(function(){
    Route::get('/users','index');
    Route::post('/user','register');
    Route::get('/user/{user}','show');
    Route::get('/user/{user}/peticiones','listPeticiones');
});



Route::controller(PeticioneController::class)->group(function(){
    Route::get('/peticiones','index');
    Route::post('/peticione','store');
    Route::get('/peticione/{peticione}','show');
    Route::post('/peticione/firmar/{peticione}/user/{user}','firmar');
    Route::get('/peticione/{peticione}/categoria','verCategoria');
    Route::get('/peticione/{peticione}/user','verUsuariosFirmantes');
    Route::put('/peticione/{peticione}/estado','updateEstado');
    Route::put('/peticione/{peticione}','update');
    Route::delete('/peticione/{peticione}','delete');

});


