<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EncuestaController;


Route::get('/', function () {
    return view('auth/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    #importar el excel
    Route::get('/encuesta', [EncuestaController::class, 'index'])->name('encuesta.index');
    Route::post('/encuesta/importar', [EncuestaController::class, 'importar'])->name('encuesta.importar');

    #ver los datos importados
    Route::get('/encuestas/mostrar', [EncuestaController::class, 'mostrarEncuestas'])->name('encuestas.mostrar');


    // Ruta para listar los profesores con buscador
    Route::get('/profesores', [EncuestaController::class, 'listarProfesores'])->name('profesores.listar');

// Ruta para mostrar los detalles de un profesor
    Route::get('/profesores/{profesor}', [EncuestaController::class, 'mostrarDetallesProfesor'])->name('profesores.detalles');

    //Ruta para las carreras
    Route::get('/panel-general', [EncuestaController::class, 'panelGeneral'])->name('panel.general');
    Route::get('/carrera/{carrera}', [EncuestaController::class, 'detallesCarrera'])->name('carrera.detalles');
});

require __DIR__.'/auth.php';
