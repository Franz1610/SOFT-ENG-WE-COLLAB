<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\FinanceEntry;
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
            'status' => 'pending', // Mark as pending when created - requires admin approval
        ]);

        return redirect()->back()->with('success', 'Booking submitted successfully! Please wait for admin approval.');
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
                    'can_cancel' => $booking->status === 'pending' && $booking->booking_date >= now()->toDateString(),
                ];
            });

        return $bookings;
    }

    public function cancel($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->where('booking_date', '>=', now()->toDateString())
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or cannot be cancelled');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully');
    }

    // Admin methods for booking approval
    public function approve($id)
    {
        $booking = Booking::where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or already processed');
        }

        $booking->update(['status' => 'confirmed']);

        return back()->with('success', 'Booking approved successfully');
    }

    public function reject($id)
    {
        $booking = Booking::where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or already processed');
        }

        $booking->update(['status' => 'rejected']);

        return back()->with('success', 'Booking rejected successfully');
    }

    public function adminCancel($id)
    {
        $booking = Booking::where('id', $id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or cannot be cancelled');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully');
    }

    /**
     * Show the payment page for a booking.
     * Lightweight placeholder: renders an Inertia page with booking details.
     */
    public function pay($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$booking) {
            return redirect('/booking/history')->with('error', 'Booking not found');
        }

        // Only allow payment for confirmed/completed bookings (admin accepted)
        if (!in_array($booking->status, ['confirmed', 'completed'])) {
            return redirect('/booking/history')->with('error', 'This booking is not eligible for payment');
        }

        return Inertia::render('BookingPayment', [
            'booking' => [
                'id' => $booking->id,
                'date' => $booking->booking_date->format('F j, Y'),
                'time' => $booking->formatted_time,
                'status' => $this->mapStatus($booking->status),
                'amount' => $booking->amount ?? null,
            ],
        ]);
    }

    /**
     * Handle payment submission with optional proof image.
     */
    public function submitPayment(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$booking) {
            return redirect('/booking/history')->with('error', 'Booking not found');
        }

        // Only allow payment for confirmed/completed bookings
        if (!in_array($booking->status, ['confirmed', 'completed'])) {
            return redirect('/booking/history')->with('error', 'This booking is not eligible for payment');
        }

        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:1'],
            'reference_no' => ['required', 'string', 'max:255'],
            'proof' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'], // 5MB
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            // Store on public disk so it can be served; requires `php artisan storage:link` once
            $proofPath = $request->file('proof')->store('payment_proofs', 'public');
        }

        // Create or update a FinanceEntry for this booking
        $entry = FinanceEntry::where('booking_id', $booking->id)->first();
        $notes = 'GCash Ref: ' . $validated['reference_no'];
        if ($proofPath) {
            $notes .= ' | proof: storage/' . $proofPath;
        }

        if ($entry) {
            // Incremental payment: accumulate amount and append notes
            $entry->amount_received = (float)$entry->amount_received + (float)$validated['amount_paid'];
            $entry->gross_total = (float)$entry->gross_total + (float)$validated['amount_paid'];
            $entry->reference_notes = trim(($entry->reference_notes ?: '') . "\n" . $notes);
            $entry->net_revenue = (float)$entry->amount_received - (float)$entry->gateway_fee - (float)$entry->tax_collected;
            $entry->save();
        } else {
            FinanceEntry::create([
                'booking_id' => $booking->id,
                'customer_name' => $booking->first_name . ' ' . $booking->last_name,
                'gross_total' => $validated['amount_paid'],
                'transaction_date' => now()->toDateString(),
                'amount_received' => $validated['amount_paid'],
                'payment_method' => 'Other', // GCash treated as other for now
                'gateway_fee' => 0,
                'tax_collected' => 0,
                'reference_notes' => $notes,
                'net_revenue' => $validated['amount_paid'],
                'status' => 'Pending Review',
                'created_by' => auth()->id(),
                'reviewed_by' => null,
            ]);
        }

        return redirect('/booking/history')->with('success', 'Payment submitted. Your proof is pending review.');
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
            'rejected' => 'Rejected',
            'completed' => 'Done',
        ];

        return $statusMap[$status] ?? 'Unknown';
    }
}
