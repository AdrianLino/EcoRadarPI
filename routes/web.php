<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EncuestaController;


#importar el excel
Route::get('/encuesta', [EncuestaController::class, 'index'])->name('encuesta.index');
Route::post('/encuesta/importar', [EncuestaController::class, 'importar'])->name('encuesta.importar');

#ver los datos importados
Route::get('/encuestas/mostrar', [EncuestaController::class, 'mostrarEncuestas'])->name('encuestas.mostrar');



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profesores', function () { return view('reportes/profesores');})->name('profesores');
});

require __DIR__.'/auth.php';
