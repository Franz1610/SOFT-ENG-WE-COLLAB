<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomManagementController extends Controller
{
    public function index()
    {
        $roomCategories = $this->getRoomCategories();
        
        return Inertia::render('admin/RoomManagement', [
            'roomCategories' => $roomCategories
        ]);
    }

    /**
     * Return the list of available rooms for a category within a date/time range.
     * Request params: category (individual|common|master), date (Y-m-d), start_time (e.g., "02:00 PM"), end_time
     * Response: [{ id: number, number: string, actual: string }]
     */
    public function getAvailableRooms(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:individual,common,master',
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
        ]);

        // Parse times into 24h HH:MM:SS using tolerant parser
        $start = $this->toHms($validated['start_time']);
        $end = $this->toHms($validated['end_time']);

        if (!$start || !$end || $start >= $end) {
            return response()->json(['rooms' => [], 'message' => 'Invalid time range'], 422);
        }

        $date = Carbon::parse($validated['date'])->toDateString();
        // Date must be today or later
        if ($date < Carbon::today()->toDateString()) {
            return response()->json(['rooms' => [], 'message' => 'Date cannot be in the past'], 422);
        }
        $category = $validated['category'];

        // Load rooms for category (exclude maintenance)
        $rooms = Room::where('category', $category)
            ->where('status', '!=', 'maintenance')
            ->get()
            ->sortBy(function ($room) {
                if (preg_match('/-(\d+)$/', $room->room_number, $m)) {
                    return (int)$m[1];
                }
                return 0;
            })
            ->values();

        $available = [];

        foreach ($rooms as $idx => $room) {
            $roomIdInt = $this->mapRoomNumberToInteger($category, $room->room_number);
            // Check conflicts with confirmed bookings only (pending should not block)
            $hasConflict = Booking::where('category', $category)
                ->where('room_id', $roomIdInt)
                ->whereDate('booking_date', $date)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($start, $end) {
                    $q->where(function ($inner) use ($start, $end) {
                        $inner->where('start_time', '<', $end)
                              ->where('end_time', '>', $start);
                    });
                })
                ->exists();

            if (!$hasConflict) {
                $available[] = [
                    'id' => $roomIdInt,
                    'number' => 'Room ' . ($idx + 1),
                    'actual' => $room->room_number,
                ];
            }
        }

        return response()->json(['rooms' => $available]);
    }

    /** Convert loose time string (e.g., "2:00 PM" or "14:00") to HH:MM:SS; null on failure. */
    private function toHms(string $time): ?string
    {
        try {
            return Carbon::createFromFormat('h:i A', $time)->format('H:i:s');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('H:i', $time)->format('H:i:s');
            } catch (\Exception $e) {
                try {
                    return Carbon::createFromFormat('H:i:s', $time)->format('H:i:s');
                } catch (\Exception $e) {
                    return null;
                }
            }
        }
    }

    public function getRoomData(Request $request, $category)
    {
        // If a specific date/time range is provided, compute statuses for that window
        $queryDate = $request->query('date');
        $queryStart = $request->query('start_time');
        $queryEnd = $request->query('end_time');

        // Get today's date using the app timezone
        $today = Carbon::today()->toDateString();
        
        // Auto-update completed bookings
        $now = Carbon::now();
        $currentTime = $now->toTimeString();
        
        // Update confirmed bookings that have ended to 'completed'
        Booking::where('status', 'confirmed')
            ->where(function ($query) use ($today, $currentTime) {
                $query->where('booking_date', '<', $today)
                      ->orWhere(function ($q) use ($today, $currentTime) {
                          $q->where('booking_date', '=', $today)
                            ->where('end_time', '<', $currentTime);
                      });
            })
            ->update(['status' => 'completed']);
        
        // Debug: Log what we're looking for
        \Log::info("Looking for bookings with category: {$category}, date: {$today}");
        
        // Get confirmed bookings for today for this category that are currently active
        $currentTime = $now->toTimeString();
        $confirmedBookings = Booking::where('category', $category)
            ->whereDate('booking_date', $today)
            ->where('status', 'confirmed')
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->with('user')
            ->get();

        // Debug: Log what we found
        \Log::info("Found confirmed bookings: " . $confirmedBookings->count());
        foreach ($confirmedBookings as $booking) {
            \Log::info("Booking ID: {$booking->id}, Room ID: {$booking->room_id}, Category: {$booking->category}, Status: {$booking->status}, Date: {$booking->booking_date}");
        }

        // Also get upcoming bookings for today (confirmed but not yet started)
        $upcomingBookings = Booking::where('category', $category)
            ->whereDate('booking_date', $today)
            ->where('status', 'confirmed')
            ->where('start_time', '>', $currentTime)
            ->with('user')
            ->get();

        // If a date/time filter is provided, compute statuses for that window instead of live view
        if ($queryDate && $queryStart && $queryEnd) {
            $selectedDate = Carbon::parse($queryDate)->toDateString();
            $start = $this->toHms($queryStart);
            $end = $this->toHms($queryEnd);
            if (!$start || !$end || $start >= $end) {
                return response()->json(['error' => 'Invalid time range'], 422);
            }

            $totalRooms = $this->getTotalRoomsByCategory($category);
            $maintenanceRooms = Room::where('category', $category)
                ->where('status', 'maintenance')
                ->get();

            $rooms = [];
            for ($i = 1; $i <= $totalRooms; $i++) {
                $roomNumber = "Room {$i}";
                $actualRoomNumber = $this->mapFrontendToActualRoomNumber($category, $roomNumber);
                $roomIdInt = $this->mapRoomNumberToInteger($category, $actualRoomNumber);

                $maintenanceRoom = $maintenanceRooms->firstWhere('room_number', $actualRoomNumber);
                if ($maintenanceRoom) {
                    $rooms[] = [
                        'number' => $roomNumber,
                        'status' => 'Maintenance',
                        'capacity' => $this->getDefaultCapacity($category, $roomNumber),
                        'guest' => null,
                        'timeRange' => null,
                        'booking_id' => null
                    ];
                    continue;
                }

                // Find a confirmed booking overlapping the requested window for this room
                $overlap = Booking::where('category', $category)
                    ->where('room_id', $roomIdInt)
                    ->whereDate('booking_date', $selectedDate)
                    ->where('status', 'confirmed')
                    ->where(function($q) use ($start, $end) {
                        $q->where(function($inner) use ($start, $end) {
                            $inner->where('start_time', '<', $end)
                                  ->where('end_time', '>', $start);
                        });
                    })
                    ->with('user')
                    ->first();

                if ($overlap) {
                    $isToday = $selectedDate === $today;
                    $nowTime = Carbon::now()->format('H:i:s');
                    $isActiveNow = $isToday && $overlap->start_time <= $nowTime && $overlap->end_time > $nowTime;
                    // Prefer account name if linked; otherwise fall back to entered names
                    $guestName = $overlap->user ? ($overlap->additional_info === 'Walk-in booking created by admin' ? 'Walk-in Guest' : $overlap->user->name) : trim(($overlap->first_name ?? '').' '.($overlap->last_name ?? ''));
                    $rooms[] = [
                        'number' => $roomNumber,
                        'status' => $isActiveNow ? 'Occupied' : 'Reserved',
                        'capacity' => $overlap->pax,
                        'guest' => $guestName,
                        'timeRange' => $this->formatTimeRange($overlap->start_time, $overlap->end_time),
                        'booking_id' => $overlap->id
                    ];
                } else {
                    $rooms[] = [
                        'number' => $roomNumber,
                        'status' => 'Available',
                        'capacity' => $this->getDefaultCapacity($category, $roomNumber),
                        'guest' => null,
                        'timeRange' => null,
                        'booking_id' => null
                    ];
                }
            }

            return response()->json($rooms);
        }

        // Default/live view for TODAY (no filters): compute active and upcoming
        // Get total number of rooms for this category
        $totalRooms = $this->getTotalRoomsByCategory($category);
        
        // Get maintenance rooms for this category
        $maintenanceRooms = Room::where('category', $category)
            ->where('status', 'maintenance')
            ->get();
        
        // Create room data with occupied/available/maintenance/reserved status
        $rooms = [];
        
        for ($i = 1; $i <= $totalRooms; $i++) {
            $roomNumber = "Room {$i}";
            
            // Get the actual room number for this position
            $actualRoomNumber = $this->mapFrontendToActualRoomNumber($category, $roomNumber);
            
            // Map to the integer room_id that would be stored in the database
            $roomIdInt = $this->mapRoomNumberToInteger($category, $actualRoomNumber);
            
            $maintenanceRoom = $maintenanceRooms->firstWhere('room_number', $actualRoomNumber);
            
            if ($maintenanceRoom) {
                $rooms[] = [
                    'number' => $roomNumber,
                    'status' => 'Maintenance',
                    'capacity' => $this->getDefaultCapacity($category, $roomNumber),
                    'guest' => null,
                    'timeRange' => null,
                    'booking_id' => null
                ];
            } else {
                // Look for a currently active booking for this specific room
                $activeBooking = $confirmedBookings->firstWhere('room_id', $roomIdInt);
                
                if ($activeBooking) {
                    $guestName = $activeBooking->user ? ($activeBooking->additional_info === 'Walk-in booking created by admin' ? 'Walk-in Guest' : $activeBooking->user->name) : trim(($activeBooking->first_name ?? '').' '.($activeBooking->last_name ?? ''));
                    \Log::info("Room {$i} (ID: {$roomIdInt}) is currently occupied by booking ID: {$activeBooking->id}");
                    $rooms[] = [
                        'number' => $roomNumber,
                        'status' => 'Occupied',
                        'capacity' => $activeBooking->pax,
                        'guest' => $guestName,
                        'timeRange' => $this->formatTimeRange($activeBooking->start_time, $activeBooking->end_time),
                        'booking_id' => $activeBooking->id
                    ];
                } else {
                    // Look for an upcoming booking for this specific room (confirmed only)
                    $upcomingBooking = $upcomingBookings->firstWhere('room_id', $roomIdInt);
                    
                    if ($upcomingBooking) {
                        $guestName = $upcomingBooking->user ? ($upcomingBooking->additional_info === 'Walk-in booking created by admin' ? 'Walk-in Guest' : $upcomingBooking->user->name) : trim(($upcomingBooking->first_name ?? '').' '.($upcomingBooking->last_name ?? ''));
                        \Log::info("Room {$i} (ID: {$roomIdInt}) is reserved for upcoming booking ID: {$upcomingBooking->id}");
                        $rooms[] = [
                            'number' => $roomNumber,
                            'status' => 'Reserved',
                            'capacity' => $upcomingBooking->pax,
                            'guest' => $guestName,
                            'timeRange' => $this->formatTimeRange($upcomingBooking->start_time, $upcomingBooking->end_time),
                            'booking_id' => $upcomingBooking->id
                        ];
                    } else {
                        // Room is available
                        $rooms[] = [
                            'number' => $roomNumber,
                            'status' => 'Available',
                            'capacity' => $this->getDefaultCapacity($category, $roomNumber),
                            'guest' => null,
                            'timeRange' => null,
                            'booking_id' => null
                        ];
                    }
                }
            }
        }

        return response()->json($rooms);
    }

    public function getRoomCategories()
    {
        // Get today's date using the app timezone
        $today = Carbon::today()->toDateString();
        
        // Auto-update completed bookings
        $now = Carbon::now();
        $currentTime = $now->toTimeString();
        
        // Update confirmed bookings that have ended to 'completed'
        Booking::where('status', 'confirmed')
            ->where(function ($query) use ($today, $currentTime) {
                $query->where('booking_date', '<', $today)
                      ->orWhere(function ($q) use ($today, $currentTime) {
                          $q->where('booking_date', '=', $today)
                            ->where('end_time', '<', $currentTime);
                      });
            })
            ->update(['status' => 'completed']);
        
        $categories = [
            'individual' => [
                'id' => 1,
                'name' => 'Phone Booth Rooms',
                'category' => 'individual',
                'capacity' => '1 Person',
                'amenities' => ['WiFi', 'Desk', 'Chair', 'Power Outlet', 'Desk Lamp'],
                'description' => 'Private individual workspace for focused work and personal meetings.'
            ],
            'common' => [
                'id' => 2,
                'name' => 'Regular Tables',
                'category' => 'common',
                'capacity' => '1-4 People',
                'amenities' => ['Smart TV', 'WiFi', 'Video Conferencing', 'Whiteboard'],
                'description' => 'Collaborative space ideal for small team meetings and discussions.'
            ],
            'master' => [
                'id' => 3,
                'name' => 'Conference Rooms',
                'category' => 'master',
                'capacity' => '1-10 People',
                'amenities' => ['4K Display', 'WiFi', 'Coffee Station', 'Video Conferencing', 'Whiteboard'],
                'description' => 'Premium conference room for important meetings and presentations.'
            ]
        ];

        // Calculate statistics for each category
        foreach ($categories as $key => &$category) {
            // Get actual room count from database instead of hardcoded value
            $totalRoomsCount = Room::where('category', $key)->count();
            $category['totalRooms'] = $totalRoomsCount;

            // Count bookings that are currently active (within their time range)
            $currentlyOccupiedBookings = Booking::where('category', $key)
                ->whereDate('booking_date', $today)
                ->where('status', 'confirmed')
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>', $currentTime)
                ->count();

            // Count upcoming bookings (confirmed but not yet started)
            $reservedBookings = Booking::where('category', $key)
                ->whereDate('booking_date', $today)
                ->where('status', 'confirmed')
                ->where('start_time', '>', $currentTime)
                ->count();

            // Count confirmed bookings for today for availability calculation (pending should not block)
            $allBookingsToday = Booking::where('category', $key)
                ->whereDate('booking_date', $today)
                ->where('status', 'confirmed')
                ->count();

            $maintenanceRoomsCount = Room::where('category', $key)
                ->where('status', 'maintenance')
                ->count();

            // Available rooms should exclude rooms with confirmed bookings today
            $category['availableRooms'] = $category['totalRooms'] - $allBookingsToday - $maintenanceRoomsCount;
            // But occupied rooms only shows currently active bookings
            $category['occupiedRooms'] = $currentlyOccupiedBookings;
            // Reserved rooms shows upcoming bookings
            $category['reservedRooms'] = $reservedBookings;
            $category['maintenanceRooms'] = $maintenanceRoomsCount;
        }

        return array_values($categories);
    }

    private function getTotalRoomsByCategory($category)
    {
        // Return the real count; if none exist, it's 0 (admin should add rooms via Room Management)
        return Room::where('category', $category)->count();
    }

    private function getDefaultCapacity($category, $roomNumber = null)
    {
        $capacities = [
            'individual' => 1,
            'common' => 4,
            'master' => 10
        ];

        return $capacities[$category] ?? 1;
    }

    private function formatTimeRange($startTime, $endTime)
    {
        // Format time range from booking data
        return Carbon::parse($startTime)->format('g:i A') . ' - ' . Carbon::parse($endTime)->format('g:i A');
    }

    public function debugBookings()
    {
        $today = Carbon::today()->toDateString();
        
        $bookings = Booking::whereDate('booking_date', $today)
            ->where('status', 'confirmed')
            ->get();

        return response()->json([
            'server_date' => $today,
            'server_timezone' => config('app.timezone'),
            'confirmed_bookings' => $bookings->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'category' => $booking->category,
                    'room_id' => $booking->room_id,
                    'first_name' => $booking->first_name,
                    'last_name' => $booking->last_name,
                    'booking_date' => $booking->booking_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'status' => $booking->status,
                    'pax' => $booking->pax
                ];
            })
        ]);
    }

    public function updateMaintenanceStatus(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:individual,master,common',
            'room_number' => 'required|string',
            'status' => 'required|in:Available,Maintenance'
        ]);

        $category = $validated['category'];
        $roomNumber = $validated['room_number'];
        
        // Map frontend room number (Room 1, Room 2) to actual database room number (IND-01, COM-01, etc)
        $actualRoomNumber = $this->mapFrontendToActualRoomNumber($category, $roomNumber);
        
        \Log::info("Frontend requested maintenance update for: {$roomNumber} in category: {$category}");
        \Log::info("Mapped to actual room number: {$actualRoomNumber}");

        // Convert the frontend status to database status
        $dbStatus = $validated['status'] === 'Maintenance' ? 'maintenance' : 'available';

        // Find the specific room - don't create if it doesn't exist
        $room = Room::where('category', $category)
            ->where('room_number', $actualRoomNumber)
            ->first();

        if (!$room) {
            \Log::error("Room not found: {$actualRoomNumber} in category: {$category}");
            return response()->json([
                'success' => false,
                'message' => "Room not found: {$actualRoomNumber}"
            ], 404);
        }

        // If setting to maintenance, ensure there is no active or upcoming confirmed booking today
        if ($dbStatus === 'maintenance') {
            $today = Carbon::today()->toDateString();
            $roomIdInt = $this->mapRoomNumberToInteger($category, $actualRoomNumber);
            $hasConflict = Booking::where('category', $category)
                ->where('room_id', $roomIdInt)
                ->whereDate('booking_date', '>=', $today)
                ->where('status', 'confirmed')
                ->exists();
            if ($hasConflict) {
                \Log::warning("Attempt to set maintenance while bookings exist for {$actualRoomNumber}");
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set to maintenance: there are confirmed bookings scheduled for this room.'
                ], 400);
            }
        }

        // Update the room status
        $room->update(['status' => $dbStatus]);
        
        \Log::info("Successfully updated room {$actualRoomNumber} status to {$dbStatus}");

        return response()->json(['success' => true]);
    }

    public function addRooms(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:individual,common,master',
            'numberOfRooms' => 'required|integer|min:1|max:50'
        ]);

        $category = $validated['category'];
        $numberOfRooms = $validated['numberOfRooms'];

        // Get existing rooms for this category to determine the next room number
        $existingRooms = Room::where('category', $category)
            ->pluck('room_number')
            ->toArray();

        // Determine the highest number and prefix based on category
        $lastNumber = 0;
        $prefix = '';

        // Set consistent prefixes for all categories
        switch ($category) {
            case 'individual':
                $prefix = 'IND';
                break;
            case 'common':
                $prefix = 'COM';
                break;
            case 'master':
                $prefix = 'MAS';
                break;
            default:
                $prefix = strtoupper(substr($category, 0, 3));
                break;
        }

        // Find the highest number for the current prefix pattern
        foreach ($existingRooms as $roomNumber) {
            if (preg_match('/' . $prefix . '-(\d+)/', $roomNumber, $matches)) {
                $lastNumber = max($lastNumber, (int)$matches[1]);
            }
        }

        // Create new rooms
        $roomsCreated = [];
        for ($i = 1; $i <= $numberOfRooms; $i++) {
            $newRoomNumber = $lastNumber + $i;
            
            // Format room number with consistent PREFIX-XX format for all categories
            $formattedRoomNumber = $prefix . '-' . str_pad($newRoomNumber, 2, '0', STR_PAD_LEFT);
            
            $room = Room::create([
                'category' => $category,
                'room_number' => $formattedRoomNumber,
                'status' => 'available'
            ]);
            
            $roomsCreated[] = $room->room_number;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully added {$numberOfRooms} room(s) to {$category} category",
            'rooms_created' => $roomsCreated
        ]);
    }

    public function deleteRoom(Request $request)
    {
        // Log the incoming request for debugging
        \Log::info('Delete room request received', $request->all());

        $validated = $request->validate([
            'category' => 'required|string',
            'room_number' => 'required|string'
        ]);

        $category = $validated['category'];
        $roomNumber = $validated['room_number'];

        \Log::info("Frontend requested to delete: {$roomNumber} in category: {$category}");
        
        // Map frontend room number (Room 1, Room 2) to actual database room number (IND-01, COM-01, etc)
        $actualRoomNumber = $this->mapFrontendToActualRoomNumber($category, $roomNumber);
        \Log::info("Mapped to actual room number: {$actualRoomNumber}");
        
        // Log all existing rooms for this category
        $existingRooms = Room::where('category', $category)->pluck('room_number');
        \Log::info("Existing rooms in {$category}: " . $existingRooms->toJson());

        // Check if room exists using the actual room number
        $room = Room::where('category', $category)
            ->where('room_number', $actualRoomNumber)
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => "Room not found: {$actualRoomNumber}"
            ], 404);
        }

        // Check if room is occupied now or has any upcoming confirmed bookings (today or future)
        $today = Carbon::today()->toDateString();
        $hasConfirmed = Booking::where('category', $category)
            ->where('room_id', $this->mapRoomNumberToInteger($category, $actualRoomNumber))
            ->whereDate('booking_date', '>=', $today)
            ->where('status', 'confirmed')
            ->exists();

        if ($hasConfirmed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete room with confirmed bookings scheduled or active.'
            ], 400);
        }

        // Delete the room
        $room->delete();

        \Log::info("Room {$roomNumber} deleted successfully");

        return response()->json([
            'success' => true,
            'message' => "Room {$roomNumber} has been successfully deleted"
        ]);
    }

    private function renumberRooms($category)
    {
        \Log::info("Starting renumbering for category: {$category}");
        
        // Set prefix based on category
        switch($category) {
            case 'individual':
                $prefix = 'IND';
                break;
            case 'common':
                $prefix = 'COM';
                break;
            case 'master':
                $prefix = 'MAS';
                break;
            default:
                $prefix = strtoupper(substr($category, 0, 3));
                break;
        }

        // Get all rooms for this category, ordered by room number
        $rooms = Room::where('category', $category)->get();
        \Log::info("Found {$rooms->count()} rooms for category {$category}");
        
        $sortedRooms = $rooms->sortBy(function ($room) use ($prefix) {
                // Extract number from room number for proper sorting
                if (preg_match('/' . $prefix . '-(\d+)/', $room->room_number, $matches)) {
                    return (int)$matches[1];
                }
                return 0;
            })
            ->values(); // Reset array keys

        \Log::info("Sorted rooms: " . $sortedRooms->pluck('room_number')->toJson());

        // Renumber all rooms sequentially starting from 1
        foreach ($sortedRooms as $index => $room) {
            $newNumber = $index + 1;
            $newRoomNumber = $prefix . '-' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
            
            \Log::info("Processing room {$room->room_number} -> {$newRoomNumber}");
            
            // Only update if the room number has changed
            if ($room->room_number !== $newRoomNumber) {
                $room->update(['room_number' => $newRoomNumber]);
                \Log::info("Updated room from {$room->room_number} to {$newRoomNumber}");
            }
        }
        
        \Log::info("Renumbering completed for category: {$category}");
    }

    private function mapFrontendToActualRoomNumber($category, $frontendRoomNumber)
    {
        // Extract number from "Room X" format
        if (preg_match('/Room (\d+)/', $frontendRoomNumber, $matches)) {
            $roomIndex = (int)$matches[1];
            
            // Get the actual room numbers from database in order
            $actualRooms = Room::where('category', $category)
                ->get()
                ->sortBy(function ($room) {
                    // Sort by the numeric part of room number for proper ordering
                    if (preg_match('/(\w+)-(\d+)/', $room->room_number, $matches)) {
                        return (int)$matches[2];
                    }
                    return 0;
                })
                ->values();
            
            // Map Room 1 -> first actual room, Room 2 -> second actual room, etc.
            if (isset($actualRooms[$roomIndex - 1])) {
                return $actualRooms[$roomIndex - 1]->room_number;
            }
        }
        
        // If not in "Room X" format or not found, return as-is
        return $frontendRoomNumber;
    }

    public function occupyRoom(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:individual,master,common',
            'room_number' => 'required|string',
            'guest_name' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string'
        ]);

        try {
            // Map frontend room number to actual database room number
            $actualRoomNumber = $this->mapFrontendToActualRoomNumber($validated['category'], $validated['room_number']);
            
            // Get today's date
            $today = Carbon::today()->toDateString();
            
            // For walk-ins, occupancy should start NOW (server time). Ignore provided start_time.
            $now = Carbon::now();
            $startTimeHms = $now->format('H:i');
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $validated['end_time']);
            if ($endDateTime->lt($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'End time cannot be in the past.'
                ], 400);
            }
            
            // Check if the room is already booked for the specified time
            $roomIdInt = $this->mapRoomNumberToInteger($validated['category'], $actualRoomNumber);
            
            $conflictingBooking = Booking::where('category', $validated['category'])
                ->where('room_id', $roomIdInt) // Use integer ID for conflict check
                ->whereDate('booking_date', $today)
                ->where('status', 'confirmed')
                ->where(function ($query) use ($startTimeHms, $validated) {
                    $query->whereBetween('start_time', [$startTimeHms, $validated['end_time']])
                          ->orWhereBetween('end_time', [$startTimeHms, $validated['end_time']])
                          ->orWhere(function ($q) use ($startTimeHms, $validated) {
                              $q->where('start_time', '<=', $startTimeHms)
                                ->where('end_time', '>=', $validated['end_time']);
                          });
                })
                ->exists();

            if ($conflictingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is already booked for the specified time.'
                ], 400);
            }

            // Check if room is in maintenance
            $room = Room::where('category', $validated['category'])
                ->where('room_number', $actualRoomNumber)
                ->first();

            if ($room && $room->status === 'maintenance') {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is currently under maintenance.'
                ], 400);
            }

            // Create the walk-in booking
            // Map actual room number to integer ID for database storage
            $roomIdInt = $this->mapRoomNumberToInteger($validated['category'], $actualRoomNumber);
            
            $booking = Booking::create([
                'user_id' => auth()->id(), // Admin who created the walk-in booking
                'first_name' => $validated['guest_name'],
                'last_name' => '', // Walk-in guests might not have last names
                'contact' => '', // Walk-in bookings might not have contact info
                'email' => '', // Walk-in bookings might not have email
                'additional_info' => 'Walk-in booking created by admin',
                'pax' => $this->getDefaultCapacity($validated['category']),
                'category' => $validated['category'],
                'room_id' => $roomIdInt, // Use integer ID
                'booking_date' => $today,
                'start_time' => $startTimeHms, // Occupy from NOW
                'end_time' => $validated['end_time'],
                'status' => 'confirmed', // Walk-in bookings are automatically confirmed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Walk-in booking created successfully',
                'booking_id' => $booking->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating walk-in booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create walk-in booking. Please try again.'
            ], 500);
        }
    }

    private function mapRoomNumberToInteger($category, $roomNumber)
    {
        // Create a consistent mapping from room number strings to integers
        // Format: [category_prefix][room_number] → integer
        // Examples: IND-01 → 1001, IND-02 → 1002, COM-01 → 2001, MAS-01 → 3001
        
        $categoryMap = [
            'individual' => 1000,
            'common' => 2000,
            'master' => 3000
        ];
        
        $baseId = $categoryMap[$category] ?? 1000;
        
        // Extract numeric part from room number (supports "IND-01" and "Room 1" formats)
        if (preg_match('/\-(\d+)$/', $roomNumber, $matches)) {
            $roomNum = (int)$matches[1];
            return $baseId + $roomNum;
        }
        if (preg_match('/Room\s+(\d+)/i', $roomNumber, $matches)) {
            $roomNum = (int)$matches[1];
            return $baseId + $roomNum;
        }
        
        // Fallback
        return $baseId + 1;
    }

    public function extendRoom(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|string|in:individual,common,master',
                'room_number' => 'required|string',
                'booking_id' => 'nullable|integer',
                'new_end_time' => 'required|string'
            ]);

            // Find the booking to extend
            $booking = null;
            
            if ($validated['booking_id']) {
                // Use booking_id if provided (preferred method)
                $booking = Booking::where('id', $validated['booking_id'])
                    ->where('status', 'confirmed')
                    ->first();
            } else {
                // Fallback: find by room and current time
                $today = Carbon::today();
                $roomIdInt = $this->mapRoomNumberToInteger($validated['category'], $validated['room_number']);
                
                $booking = Booking::where('room_id', $roomIdInt)
                    ->where('booking_date', $today)
                    ->where('status', 'confirmed')
                    ->whereTime('start_time', '<=', now())
                    ->whereTime('end_time', '>', now())
                    ->first();
            }

            if (!$booking) {
                \Log::error('No booking found for extend request', [
                    'booking_id' => $validated['booking_id'],
                    'category' => $validated['category'],
                    'room_number' => $validated['room_number']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No active booking found for this room.'
                ], 404);
            }

            // Validate that new end time is later than current end time
            $currentEndTime = Carbon::parse($booking->end_time);
            $newEndTime = Carbon::parse($validated['new_end_time']);
            
            if ($newEndTime <= $currentEndTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'New end time must be later than the current end time.'
                ], 400);
            }

            // Check for conflicts with other bookings on the same room
            $today = Carbon::parse($booking->booking_date);
            $conflicts = Booking::where('room_id', $booking->room_id)
                ->where('booking_date', $today)
                ->where('status', 'confirmed')
                ->where('id', '!=', $booking->id) // Exclude current booking
                ->where(function($query) use ($currentEndTime, $newEndTime) {
                    $query->whereBetween('start_time', [$currentEndTime, $newEndTime])
                        ->orWhereBetween('end_time', [$currentEndTime, $newEndTime])
                        ->orWhere(function($q) use ($currentEndTime, $newEndTime) {
                            $q->where('start_time', '<=', $currentEndTime)
                              ->where('end_time', '>=', $newEndTime);
                        });
                })
                ->exists();

            if ($conflicts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot extend booking due to conflicts with other bookings during the requested time.'
                ], 400);
            }

            // Update the booking end time
            $booking->update([
                'end_time' => $validated['new_end_time']
            ]);

            \Log::info('Booking extended successfully', [
                'booking_id' => $booking->id,
                'old_end_time' => $currentEndTime->format('H:i'),
                'new_end_time' => $validated['new_end_time']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking extended successfully',
                'new_end_time' => $validated['new_end_time']
            ]);

        } catch (\Exception $e) {
            \Log::error('Error extending booking: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to extend booking. Please try again.'
            ], 500);
        }
    }

    public function stopRoom(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|string|in:individual,common,master',
                'room_number' => 'required|string',
                'booking_id' => 'required|integer'
            ]);

            // Find the booking to stop/cancel
            $booking = Booking::where('id', $validated['booking_id'])
                ->where('status', 'confirmed')
                ->first();

            if (!$booking) {
                \Log::error('No confirmed booking found for stop request', [
                    'booking_id' => $validated['booking_id'],
                    'category' => $validated['category'],
                    'room_number' => $validated['room_number']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No active booking found for this room.'
                ], 404);
            }

            // Check if the booking is currently active (started but not ended)
            $now = Carbon::now();
            $bookingDate = Carbon::parse($booking->booking_date);
            $startTime = Carbon::parse($booking->start_time);
            $endTime = Carbon::parse($booking->end_time);
            
            // Create full datetime objects for comparison
            $bookingStart = $bookingDate->copy()->setTimeFromTimeString($startTime->format('H:i:s'));
            $bookingEnd = $bookingDate->copy()->setTimeFromTimeString($endTime->format('H:i:s'));

            if ($now < $bookingStart) {
                // Future booking - can be canceled
                $statusMessage = 'Future booking has been canceled';
            } elseif ($now >= $bookingStart && $now <= $bookingEnd) {
                // Currently active booking - can be stopped
                $statusMessage = 'Active booking has been stopped and canceled';
            } else {
                // Past booking - shouldn't happen but handle gracefully
                return response()->json([
                    'success' => false,
                    'message' => 'This booking has already ended and cannot be stopped.'
                ], 400);
            }

            // Update the booking status to canceled
            $booking->update([
                'status' => 'cancelled'
            ]);

            \Log::info('Booking stopped/canceled successfully', [
                'booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'guest' => $booking->first_name . ' ' . $booking->last_name,
                'original_time_range' => $booking->start_time . ' - ' . $booking->end_time,
                'canceled_at' => $now->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => $statusMessage
            ]);

        } catch (\Exception $e) {
            \Log::error('Error stopping booking: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Admin utility: clear upcoming/today confirmed bookings for specific rooms.
     * This removes unintended "Reserved" state derived from such bookings.
     */
    public function clearRoomReservations(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:individual,common,master',
            'rooms' => 'required|array|min:1',
            'rooms.*' => 'string'
        ]);

        $today = Carbon::today()->toDateString();
        $updatedTotal = 0;

        foreach ($validated['rooms'] as $roomRef) {
            // Support both "Room X" and "PREFIX-XX" inputs
            $actualRoomNumber = $this->mapFrontendToActualRoomNumber($validated['category'], $roomRef);
            $roomIdInt = $this->mapRoomNumberToInteger($validated['category'], $actualRoomNumber);

            // Cancel any confirmed bookings for today and future dates on this room
            $updated = Booking::where('category', $validated['category'])
                ->where('room_id', $roomIdInt)
                ->whereDate('booking_date', '>=', $today)
                ->where('status', 'confirmed')
                ->update(['status' => 'cancelled']);

            $updatedTotal += $updated;
        }

        return response()->json([
            'success' => true,
            'cancelled_count' => $updatedTotal
        ]);
    }
}