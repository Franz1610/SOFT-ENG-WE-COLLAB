<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\FeedbackController;

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
    // Routes for both Admin and Admin Officer (general admin access)
    Route::middleware('admin')->group(function () {
        // Dashboard access - both admin and admin officer
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
        
        // Room management - both admin and admin officer
        Route::get('/admin/rooms', [\App\Http\Controllers\RoomManagementController::class, 'index']);
        Route::get('/admin/rooms/{category}', [\App\Http\Controllers\RoomManagementController::class, 'getRoomData']);
        Route::post('/admin/rooms/maintenance', [\App\Http\Controllers\RoomManagementController::class, 'updateMaintenanceStatus']);
        
        // Booking management - both admin and admin officer
        Route::post('/admin/bookings/{id}/approve', [\App\Http\Controllers\BookingController::class, 'approve']);
        Route::post('/admin/bookings/{id}/reject', [\App\Http\Controllers\BookingController::class, 'reject']);
        Route::post('/admin/bookings/{id}/cancel', [\App\Http\Controllers\BookingController::class, 'adminCancel']);
        
        // Debug route - both admin and admin officer
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
    });
    
    // Routes for Admin and Admin Officer (user management)
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [\App\Http\Controllers\UserController::class, 'index']);
        Route::get('/admin/users/{id}/edit', [\App\Http\Controllers\UserController::class, 'edit']);
        Route::put('/admin/users/{id}', [\App\Http\Controllers\UserController::class, 'update']);
        Route::delete('/admin/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
        Route::post('/admin/users/{id}/toggle-block', [\App\Http\Controllers\UserController::class, 'toggleBlock']);
    });

    // Routes ONLY for Admin Officer (role management)
    Route::middleware('admin_officer')->group(function () {
        Route::get('/admin/user-roles', [\App\Http\Controllers\Admin\UserRoleController::class, 'index'])->name('admin.user-roles.index');
        Route::post('/admin/users/{id}/role', [\App\Http\Controllers\Admin\UserRoleController::class, 'updateRole'])->name('admin.users.update-role');
    });

    // Routes ONLY for Admin Officer (finance access)
    Route::middleware('admin_officer')->group(function () {
        Route::get('/admin/finance', [FinanceController::class, 'index'])->name('admin.finance.index');
        Route::get('/admin/finance/create', [FinanceController::class, 'create'])->name('admin.finance.create');
        Route::post('/admin/finance', [FinanceController::class, 'store'])->name('admin.finance.store');
        Route::post('/admin/finance/{id}/verify', [FinanceController::class, 'verify'])->name('admin.finance.verify');
        
        // Transaction management routes
        Route::post('/admin/finance/transactions', [FinanceController::class, 'storeTransaction'])->name('admin.finance.transactions.store');
        Route::put('/admin/finance/transactions/{id}', [FinanceController::class, 'updateTransaction'])->name('admin.finance.transactions.update');
        Route::delete('/admin/finance/transactions/{id}', [FinanceController::class, 'deleteTransaction'])->name('admin.finance.transactions.delete');
    });

    // Feedback Management routes for Admin Officer
    Route::middleware(['auth', 'admin_officer'])->prefix('admin')->group(function () {
        Route::get('feedback', [FeedbackController::class, 'index'])->name('admin.feedback.index');
        Route::delete('feedback/{id}', [FeedbackController::class, 'destroy'])->name('admin.feedback.destroy');
    });
});

// Serve the survey create page to match resources/js/pages/survey/Create.vue (requires auth)
Route::get('/survey/create', function () {
    return Inertia::render('Create');
})->middleware(['auth', 'verified']);

// Feedback routes
Route::middleware(['auth'])->group(function () {
    Route::get('/feedback/create', function () {
        return Inertia::render('Create');
    })->name('feedback.create');
    Route::post('/feedback', [\App\Http\Controllers\FeedbackSubmissionController::class, 'store'])->name('feedback.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
