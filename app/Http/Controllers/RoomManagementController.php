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

    public function getRoomData($category)
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
        
        // Debug: Log what we're looking for
        \Log::info("Looking for bookings with category: {$category}, date: {$today}");
        
        // Get confirmed bookings for today for this category
        $confirmedBookings = Booking::where('category', $category)
            ->whereDate('booking_date', $today)
            ->where('status', 'confirmed')
            ->get();

        // Debug: Log what we found
        \Log::info("Found confirmed bookings: " . $confirmedBookings->count());
        foreach ($confirmedBookings as $booking) {
            \Log::info("Booking ID: {$booking->id}, Room ID: {$booking->room_id}, Category: {$booking->category}, Status: {$booking->status}, Date: {$booking->booking_date}");
        }

        // Get total number of rooms for this category
        $totalRooms = $this->getTotalRoomsByCategory($category);
        
        // Get maintenance rooms for this category
        $maintenanceRooms = Room::where('category', $category)
            ->where('status', 'maintenance')
            ->get();
        
        // Create room data with occupied/available/maintenance status
        $rooms = [];
        $bookingIndex = 0; // Track which booking to assign to which room
        
        for ($i = 1; $i <= $totalRooms; $i++) {
            $roomNumber = "Room {$i}";
            
            // Get the actual room number for this position
            $actualRoomNumber = $this->mapFrontendToActualRoomNumber($category, $roomNumber);
            
            // Check if this room is in maintenance
            $maintenanceRoom = $maintenanceRooms->firstWhere('room_number', $actualRoomNumber);
            
            if ($maintenanceRoom) {
                $rooms[] = [
                    'number' => $roomNumber,
                    'status' => 'Maintenance',
                    'capacity' => $this->getDefaultCapacity($category),
                    'guest' => null,
                    'timeRange' => null,
                    'booking_id' => null
                ];
            } elseif ($bookingIndex < $confirmedBookings->count()) {
                // Assign bookings to non-maintenance rooms
                $booking = $confirmedBookings[$bookingIndex];
                \Log::info("Room {$i} is occupied by booking ID: {$booking->id}");
                $rooms[] = [
                    'number' => $roomNumber,
                    'status' => 'Occupied',
                    'capacity' => $booking->pax,
                    'guest' => $booking->first_name . ' ' . $booking->last_name,
                    'timeRange' => $this->formatTimeRange($booking->start_time, $booking->end_time),
                    'booking_id' => $booking->id
                ];
                $bookingIndex++;
            } else {
                $rooms[] = [
                    'number' => $roomNumber,
                    'status' => 'Available',
                    'capacity' => $this->getDefaultCapacity($category),
                    'guest' => null,
                    'timeRange' => null,
                    'booking_id' => null
                ];
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
                'name' => 'Individual Room',
                'category' => 'individual',
                'capacity' => '1 Person',
                'amenities' => ['WiFi', 'Desk', 'Chair', 'Power Outlet', 'Desk Lamp'],
                'description' => 'Private individual workspace for focused work and personal meetings.'
            ],
            'common' => [
                'id' => 2,
                'name' => 'Common Room',
                'category' => 'common',
                'capacity' => '3-5 People',
                'amenities' => ['Smart TV', 'WiFi', 'Video Conferencing', 'Whiteboard'],
                'description' => 'Collaborative space ideal for small team meetings and discussions.'
            ],
            'master' => [
                'id' => 3,
                'name' => 'Master Room',
                'category' => 'master',
                'capacity' => '5-10 People',
                'amenities' => ['4K Display', 'WiFi', 'Coffee Station', 'Video Conferencing', 'Whiteboard'],
                'description' => 'Premium conference room for important meetings and presentations.'
            ]
        ];

        // Calculate statistics for each category
        foreach ($categories as $key => &$category) {
            // Get actual room count from database instead of hardcoded value
            $totalRoomsCount = Room::where('category', $key)->count();
            $category['totalRooms'] = $totalRoomsCount;

            $confirmedBookings = Booking::where('category', $key)
                ->whereDate('booking_date', $today)
                ->where('status', 'confirmed')
                ->count();

            $maintenanceRoomsCount = Room::where('category', $key)
                ->where('status', 'maintenance')
                ->count();

            $category['availableRooms'] = $category['totalRooms'] - $confirmedBookings - $maintenanceRoomsCount;
            $category['occupiedRooms'] = $confirmedBookings;
            $category['maintenanceRooms'] = $maintenanceRoomsCount;
        }

        return array_values($categories);
    }

    private function getTotalRoomsByCategory($category)
    {
        return Room::where('category', $category)->count() ?: 1;
    }

    private function getDefaultCapacity($category)
    {
        $capacities = [
            'individual' => 1,
            'common' => 4,
            'master' => 8
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

        // Update the room status
        $room->update(['status' => $dbStatus]);
        
        \Log::info("Successfully updated room {$actualRoomNumber} status to {$dbStatus}");

        return response()->json(['success' => true]);
    }

    public function addRooms(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
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

        // Check if room is occupied (has active bookings)
        $today = Carbon::today()->toDateString();
        $activeBooking = Booking::where('category', $category)
            ->where('room_id', $actualRoomNumber)
            ->whereDate('booking_date', $today)
            ->where('status', 'confirmed')
            ->exists();

        if ($activeBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete room with active bookings'
            ], 400);
        }

        // Delete the room
        $room->delete();

        \Log::info("Room {$roomNumber} deleted successfully");

        // Renumber all rooms in this category to fill gaps
        try {
            $this->renumberRooms($category);
            \Log::info("Renumbering completed successfully");
        } catch (\Exception $e) {
            \Log::error("Error during renumbering: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Room deleted but renumbering failed: " . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Room {$roomNumber} has been successfully deleted and rooms have been renumbered"
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
}