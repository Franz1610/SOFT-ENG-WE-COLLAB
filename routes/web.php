<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();
    if (!$user || !$user->is_admin) {
        auth()->logout();
        return redirect('/login');
    }
    $users = \App\Models\User::all();
    return Inertia::render('Dashboard', [
        'users' => $users,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/booking', function () {
    return Inertia::render('Booking');
})->middleware(['auth', 'verified']);

Route::get('/booking/schedule', function () {
    return Inertia::render('BookingSchedule');
})->middleware(['auth', 'verified']);

Route::post('/booking/schedule', function (\Illuminate\Http\Request $request) {
    // Store booking form data in session
    $bookingData = $request->all();
    $request->session()->put('booking_data', $bookingData);
    return Inertia::render('BookingSchedule', $bookingData);
})->middleware(['auth', 'verified']);

Route::get('/booking/history', function () {
    $bookingController = new \App\Http\Controllers\BookingController();
    $bookings = $bookingController->getUserBookings();
    
    return Inertia::render('BookingHistory', [
        'bookings' => $bookings
    ]);
})->middleware(['auth', 'verified']);

Route::post('/booking/store', [\App\Http\Controllers\BookingController::class, 'store'])
    ->middleware(['auth', 'verified']);

Route::post('/booking/{id}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])
    ->middleware(['auth', 'verified']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [\App\Http\Controllers\UserController::class, 'index']);
        Route::get('/admin/users/{id}/edit', [\App\Http\Controllers\UserController::class, 'edit']);
        Route::put('/admin/users/{id}', [\App\Http\Controllers\UserController::class, 'update']);
        Route::delete('/admin/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
