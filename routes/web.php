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
    return Inertia::render('admin/Dashboard', [
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
        
        Route::get('/admin/bookings', function () {
            $bookings = \App\Models\Booking::with('user')
                ->orderBy('booking_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get()
                ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'user' => [
                        'id' => $booking->user->id,
                        'name' => $booking->user->name,
                        'email' => $booking->user->email,
                    ],
                    'company_name' => $booking->company_name,
                    'room' => $booking->room_id,
                    'room_name' => $booking->room_name,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'formatted_time' => $booking->formatted_time,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ];
            });
            
            return Inertia::render('admin/ManageBookings', [
                'bookings' => $bookings,
            ]);
        });
        
        Route::get('/admin/rooms', function () {
            return Inertia::render('admin/RoomManagement');
        });
        
        // Admin booking management routes
        Route::post('/admin/bookings/{id}/approve', [\App\Http\Controllers\BookingController::class, 'approve']);
        Route::post('/admin/bookings/{id}/reject', [\App\Http\Controllers\BookingController::class, 'reject']);
        Route::post('/admin/bookings/{id}/cancel', [\App\Http\Controllers\BookingController::class, 'adminCancel']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
