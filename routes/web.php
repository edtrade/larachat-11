<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
//use Illuminate\Foundation\Application;
//use Inertia\Inertia;


// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/',[HomeController::class, 'index'])->name('dashboard');

    Route::get('/user/{user}',[MessageController::class, 'byUser'])->name('chat.user');   

    Route::get('/group/{group}',[MessageController::class, 'byGroup'])->name('chat.group');

    Route::post('/message',[MessageController::class, 'store'])->name('message.store');

    Route::delete('/message/{message]',[MessageController::class, 'destroy'])->name('message.destroy');

    Route::get('/message/{message]/older',[MessageController::class, 'loadOlder'])->name('message.older');    

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
