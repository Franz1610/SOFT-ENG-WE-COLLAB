<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Public API for room availability (accessible without auth)
Route::get('/api/room-availability', [\App\Http\Controllers\RoomManagementController::class, 'getRoomCategories']);

Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/booking', function () {
    // Get room availability data
    $controller = new \App\Http\Controllers\RoomManagementController();
    $roomCategories = $controller->getRoomCategories();
    
    return Inertia::render('Booking', [
        'roomCategories' => $roomCategories
    ]);
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
        Route::post('/admin/users/{id}/toggle-block', [\App\Http\Controllers\UserController::class, 'toggleBlock']);
        
        Route::get('/admin/bookings', function () {
            // First, update bookings that have passed their end time to 'completed'
            $now = \Carbon\Carbon::now();
            $today = $now->toDateString();
            $currentTime = $now->toTimeString();
            
            // Update confirmed bookings that have ended
            \App\Models\Booking::where('status', 'confirmed')
                ->where(function ($query) use ($today, $currentTime) {
                    $query->where('booking_date', '<', $today)
                          ->orWhere(function ($q) use ($today, $currentTime) {
                              $q->where('booking_date', '=', $today)
                                ->where('end_time', '<', $currentTime);
                          });
                })
                ->update(['status' => 'completed']);
            
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
        
        Route::get('/admin/rooms', [\App\Http\Controllers\RoomManagementController::class, 'index']);
        Route::get('/admin/rooms/{category}', [\App\Http\Controllers\RoomManagementController::class, 'getRoomData']);
        Route::post('/admin/rooms/maintenance', [\App\Http\Controllers\RoomManagementController::class, 'updateMaintenanceStatus']);
        Route::get('/admin/debug-bookings', function () {
            $today = \Carbon\Carbon::today();
            $allBookings = \App\Models\Booking::where('status', 'confirmed')
                ->get(['id', 'category', 'room_id', 'first_name', 'last_name', 'booking_date', 'status', 'start_time', 'end_time']);
            
            $todayBookings = \App\Models\Booking::where('booking_date', $today)
                ->where('status', 'confirmed')
                ->get(['id', 'category', 'room_id', 'first_name', 'last_name', 'booking_date', 'status', 'start_time', 'end_time']);
            
            return response()->json([
                'today' => $today->toDateString(),
                'server_timezone' => config('app.timezone'),
                'current_time' => now()->toDateTimeString(),
                'confirmed_bookings_today' => $todayBookings,
                'all_confirmed_bookings' => $allBookings
            ]);
        });
        
        // Admin booking management routes
        Route::post('/admin/bookings/{id}/approve', [\App\Http\Controllers\BookingController::class, 'approve']);
        Route::post('/admin/bookings/{id}/reject', [\App\Http\Controllers\BookingController::class, 'reject']);
        Route::post('/admin/bookings/{id}/cancel', [\App\Http\Controllers\BookingController::class, 'adminCancel']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
