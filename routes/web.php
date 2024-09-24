<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\TeamController;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckClubLimit;
use App\Http\Middleware\IsUserBanned;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ClubController::class, 'index'])->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::middleware([CheckClubLimit::class])->group(function (){
        Route::post('/club/create', [ClubController::class, 'create'])
            ->name('club.create.post');

        Route::get('/team/accept/{id}', [TeamController::class, 'acceptInvitation'])
            ->name('team.accept');
    });

    Route::get('/club/create', function() {
        return view('club.create');
    })->name('club.create.form');

    Route::delete('/club/delete/{id}', [ClubController::class, 'delete'])
    ->name('club.delete');

    Route::put('/club/update/{id}', [ClubController::class, 'update'])
    ->name('club.update');

    Route::get('/club/update/{id}', [ClubController::class, 'updateForm'])
        ->name('club.update.form');

    Route::post('/team/join', [TeamController::class, 'sendInvitation'])
    ->name('team.join');


    Route::get('/dashboard', [TeamController::class, 'dashboard'])
        ->name('dashboard');

    Route::middleware([CheckAdmin::class])->group(function() {
        Route::get('/admin/users', [AdminController::class, 'listUsers'])
            ->name('admin.users');

        Route::get('/admin/users/banned', [AdminController::class, 'listBannedUsers'])
            ->name('admin.users.banned');

        Route::get('/admin/clubs', [AdminController::class, 'listclubs'])
            ->name('admin.clubs');

        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
            ->name('admin.dashboard');

        Route::put('/admin/ban/{id}', [AdminController::class, 'ban'])
            ->name("admin.user.ban");

        Route::put('/admin/unban/{id}', [AdminController::class, 'unban'])
            ->name("admin.user.unban");

        Route::delete('/admin/delete/{id}', [AdminController::class, 'delete'])
            ->name("admin.user.delete");
    });
});

Route::get('/club/show/{id}', [ClubController::class, 'show'])
->name('club.show');

Route::get('/{username}', [ProfileController::class, 'show'])
    ->name('user.show');


