<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'additional_info' => 'nullable|string',
            'pax' => 'required|integer|min:1',
            'category' => 'required|in:individual,master,common',
            'room_id' => 'required|integer|in:1,2,3',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
        ]);

        // Convert time format from "HH:MM AM/PM" to "HH:MM:SS"
        $startTime = $this->convertTimeFormat($validated['start_time']);
        $endTime = $this->convertTimeFormat($validated['end_time']);

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'contact' => $validated['contact'],
            'email' => $validated['email'],
            'additional_info' => $validated['additional_info'],
            'pax' => $validated['pax'],
            'category' => $validated['category'],
            'room_id' => $validated['room_id'],
            'booking_date' => $validated['booking_date'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'confirmed', // Mark as confirmed when created
        ]);

        return redirect()->back()->with('success', 'Booking created successfully!');
    }

    public function getUserBookings()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'date' => $booking->booking_date->format('F j, Y'),
                    'category' => ucfirst($booking->category) . ' room',
                    'time' => $booking->formatted_time,
                    'status' => $this->mapStatus($booking->status),
                    'can_cancel' => $booking->status === 'confirmed' && $booking->booking_date >= now()->toDateString(),
                ];
            });

        return $bookings;
    }

    public function cancel($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->where('booking_date', '>=', now()->toDateString())
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or cannot be cancelled');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully');
    }

    private function convertTimeFormat($timeString)
    {
        // Convert "10:30 AM" format to "10:30:00" format
        try {
            return Carbon::createFromFormat('g:i A', $timeString)->format('H:i:s');
        } catch (\Exception $e) {
            // If parsing fails, try other formats
            try {
                return Carbon::createFromFormat('H:i A', $timeString)->format('H:i:s');
            } catch (\Exception $e) {
                // Default fallback
                return '00:00:00';
            }
        }
    }

    private function mapStatus($status)
    {
        $statusMap = [
            'pending' => 'Pending',
            'confirmed' => 'Done',
            'cancelled' => 'Cancelled',
            'completed' => 'Done',
        ];

        return $statusMap[$status] ?? 'Unknown';
    }
}
