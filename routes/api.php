<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BarberController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

Route::post('/register', [AuthController::class, 'register']);

Route::get('/user', [UserController::class, 'read']);
Route::put('/user', [UserController::class, 'update']);
Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
Route::get('/user/favorites', [UserController::class, 'getFavorites']);
Route::post('/user/favorite', [UserController::class, 'addFavorite']);
Route::get('/user/appointments', [UserController::class, 'getAppointments']);

Route::get('/barbers', [BarberController::class, 'list']);
Route::get('/barber/{id}', [BarberController::class, 'one']);
Route::post('/barber/{id}/appointments', [BarberController::class, 'setAppointment']);

Route::get('/search', [BarberController::class, 'search']);
