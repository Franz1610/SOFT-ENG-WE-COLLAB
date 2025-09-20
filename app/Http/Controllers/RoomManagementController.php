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
            
            // Check if this room is in maintenance
            $maintenanceRoom = $maintenanceRooms->firstWhere('room_number', $roomNumber);
            
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
                'totalRooms' => 12,
                'capacity' => '1 Person',
                'amenities' => ['WiFi', 'Desk', 'Chair', 'Power Outlet', 'Desk Lamp'],
                'description' => 'Private individual workspace for focused work and personal meetings.'
            ],
            'common' => [
                'id' => 2,
                'name' => 'Common Room',
                'category' => 'common',
                'totalRooms' => 5,
                'capacity' => '3-5 People',
                'amenities' => ['Smart TV', 'WiFi', 'Video Conferencing', 'Whiteboard'],
                'description' => 'Collaborative space ideal for small team meetings and discussions.'
            ],
            'master' => [
                'id' => 3,
                'name' => 'Master Room',
                'category' => 'master',
                'totalRooms' => 3,
                'capacity' => '5-10 People',
                'amenities' => ['4K Display', 'WiFi', 'Coffee Station', 'Video Conferencing', 'Whiteboard'],
                'description' => 'Premium conference room for important meetings and presentations.'
            ]
        ];

        // Calculate statistics for each category
        foreach ($categories as $key => &$category) {
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
        $roomCounts = [
            'individual' => 12,
            'common' => 5,
            'master' => 3
        ];

        return $roomCounts[$category] ?? 1;
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

        // Convert the frontend status to database status
        $dbStatus = $validated['status'] === 'Maintenance' ? 'maintenance' : 'available';

        // Update or create room record
        Room::updateOrCreate(
            [
                'category' => $validated['category'],
                'room_number' => $validated['room_number']
            ],
            [
                'status' => $dbStatus
            ]
        );

        return response()->json(['success' => true]);
    }
}