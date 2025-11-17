<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\FinanceEntry;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'contact' => ['required','digits:11'],
            'email' => 'required|email|max:255',
            'additional_info' => 'nullable|string',
            'pax' => 'required|integer|min:1',
            'category' => 'required|in:individual,master,common',
            // Accept mapped integer room IDs (e.g., 1001+, 2001+, 3001+)
            'room_id' => 'required|integer|min:1',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
        ]);

        // Convert time format from "HH:MM AM/PM" to "HH:MM:SS"
        $startTime = $this->convertTimeFormat($validated['start_time']);
        $endTime = $this->convertTimeFormat($validated['end_time']);

        // Basic logical checks beyond form validation
        if ($startTime === '00:00:00' || $endTime === '00:00:00') {
            return back()->with('error', 'Invalid time format provided.');
        }
        if ($startTime >= $endTime) {
            return back()->with('error', 'End time must be after start time.');
        }

        // Validate that the selected room matches the selected category via mapped ID
        if (!$this->roomIdMatchesCategory((int)$validated['room_id'], $validated['category'])) {
            return back()->with('error', 'Selected room does not match the chosen room type.');
        }

        // Prevent overlapping bookings for same room and date (confirmed only to keep UX consistent)
        $overlap = Booking::where('booking_date', $validated['booking_date'])
            ->where('room_id', $validated['room_id'])
            ->where('status', 'confirmed')
            ->where(function($q) use ($startTime, $endTime) {
                $q->where(function($inner) use ($startTime, $endTime) {
                    // Existing booking starts before new end AND ends after new start => overlap
                    $inner->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'Selected time overlaps with an existing booking for this room.');
        }

        // Compute duration in whole hours (per business rule)
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
        $durationHours = max(1, (int) floor($end->diffInMinutes($start) / 60));

        // Compute price snapshot based on promo
        $estimatedPrice = $this->computeEstimatedPrice(
            $validated['category'],
            $durationHours,
            (int) $validated['pax']
        );

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
            'duration_hours' => $durationHours,
            'estimated_price' => $estimatedPrice,
        ]);

        // Return to booking history so user can see the pending booking
        return redirect('/booking/history')->with('success', 'Booking submitted successfully! Please wait for admin approval.');
    }

    /**
     * Compute the promo-based estimated price snapshot.
     */
    private function computeEstimatedPrice(string $category, int $durationHours, int $pax): ?float
    {
        $category = strtolower($category);
        // Phone booth rooms (individual)
        if ($category === 'individual') {
            $map = [1 => 70, 2 => 120, 3 => 150, 4 => 200];
            return $map[$durationHours] ?? null;
        }
        // Regular tables (common)
        if ($category === 'common') {
            $map = [1 => 39, 3 => 99, 6 => 195, 8 => 245];
            return $map[$durationHours] ?? null;
        }
        // Conference rooms (master) per hour, pax bracketed
        if ($category === 'master') {
            $perHour = $pax <= 6 ? 200 : 300;
            return $perHour * max(1, $durationHours);
        }
        return null;
    }

    public function getUserBookings()
    {
        $bookings = Booking::with(['financeEntry.creator', 'financeEntry.reviewer'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($booking) {
                // Determine payment state from finance entry
                $finance = $booking->financeEntry;
                $paid = $finance && $finance->status === 'Verified';
                $rejected = $finance && $finance->status === 'Unprocessed' && $finance->decline_reason;
                $pendingPayment = !$paid && !$rejected && $finance && $finance->status === 'Pending Review';
                $amountPaid = $finance ? (float)($finance->amount_received ?? 0) : 0.0;
                $receipt = $this->buildReceiptPayload($booking, $finance);

                return [
                    'id' => $booking->id,
                    'date' => $booking->booking_date->format('F j, Y'),
                    'category' => ucfirst($booking->category) . ' room',
                    // Show the specific room selected (Room N)
                    'room' => $this->formatRoomLabel($booking->room_id),
                    'time' => $booking->formatted_time,
                    'status' => $paid ? 'Paid' : ($rejected ? 'Rejected' : ($pendingPayment ? 'Pending Payment' : $this->mapStatus($booking->status))),
                    'created_at' => $booking->created_at ? $booking->created_at->toDateTimeString() : null,
                    'paid' => $paid,
                    'decline_reason' => $rejected ? $finance->decline_reason : null,
                    'can_cancel' => $booking->status === 'pending' && $booking->booking_date >= now()->toDateString(),
                    'duration_hours' => $booking->duration_hours,
                    'estimated_price' => $booking->estimated_price,
                    // Payment figures for the Pay modal
                    'amount_due' => $booking->estimated_price ?? null,
                    'amount_paid' => $amountPaid,
                    'receipt_available' => (bool) $receipt,
                    'receipt' => $receipt,
                ];
            });

        return $bookings;
    }

    /** Build the receipt payload exposed to the Booking History UI for verified payments. */
    private function buildReceiptPayload(Booking $booking, ?FinanceEntry $finance): ?array
    {
        if (!$finance || $finance->status !== 'Verified') {
            return null;
        }

        $invoiceNumber = sprintf('INV-%05d', $finance->id);

        return [
            'invoice_number' => $invoiceNumber,
            'booking_reference' => '#' . $booking->id,
            'transaction_date' => optional($finance->transaction_date)->format('F j, Y'),
            'customer_name' => $finance->customer_name,
            'payment_method' => $finance->payment_method,
            'gross_total' => (float) ($finance->gross_total ?? 0),
            'amount_received' => (float) ($finance->amount_received ?? 0),
            'gateway_fee' => (float) ($finance->gateway_fee ?? 0),
            'tax_collected' => (float) ($finance->tax_collected ?? 0),
            'net_revenue' => (float) ($finance->net_revenue ?? 0),
            'reference_notes' => $finance->reference_notes,
            'prepared_by' => optional($finance->creator)->name,
            'approved_by' => optional($finance->reviewer)->name,
            'status' => $finance->status,
        ];
    }

    public function downloadReceipt($id)
    {
        $booking = Booking::with(['financeEntry.creator', 'financeEntry.reviewer'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $finance = $booking->financeEntry;
        if (!$finance || $finance->status !== 'Verified') {
            abort(404);
        }

        $receipt = $this->buildReceiptPayload($booking, $finance);

        $pdf = Pdf::loadView('pdf.receipt', [
            'booking' => $booking,
            'finance' => $finance,
            'receipt' => $receipt,
        ])->setPaper('a4');

        $fileName = ($receipt['invoice_number'] ?? 'receipt') . '.pdf';

        return $pdf->download($fileName);
    }

    /** Convert stored room_id (mapped int or legacy string) to a user-friendly "Room N" label. */
    private function formatRoomLabel($roomId): ?string
    {
        if (is_null($roomId)) return null;

        // If it's the mapped integer format (1001+, 2001+, 3001+), use the suffix as room number
        if (is_numeric($roomId)) {
            $id = (int) $roomId;
            if ($id >= 1001) {
                $n = $id % 1000;
                if ($n > 0) return 'Room ' . $n;
            }
            // Legacy small numeric IDs (1,2,3) - convert to room labels
            if ($id >= 1 && $id <= 3) {
                return 'Room ' . $id;
            }
            return null;
        }

        // Legacy string format like IND-01, COM-03, MAS-10
        if (is_string($roomId) && preg_match('/-(\d+)$/', $roomId, $m)) {
            $n = (int)$m[1];
            if ($n > 0) return 'Room ' . $n;
        }

        return null;
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

    // Note: Intentionally no restore() endpoint to avoid conflict risks.

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

        // No partial payments: amount must exactly match the stored snapshot due
        $due = (float) ($booking->estimated_price ?? 0);
        if ($due <= 0) {
            return redirect('/booking/history')->with('error', 'No amount due is available for this booking.');
        }
        $amount = (float) $validated['amount_paid'];
        if (abs($amount - $due) > 0.01) {
            return redirect('/booking/history')->with('error', 'Partial payments are not allowed. Amount must match the total due.');
        }

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
            // Prevent changes after verification
            if ($entry->status === 'Verified') {
                return redirect('/booking/history')->with('error', 'Payment already verified. Updates are not allowed.');
            }

            // Reset review state for a clean resubmission
            $entry->status = 'Pending Review';
            $entry->reviewed_by = null;
            $entry->decline_reason = null;
            $entry->declined_at = null;

            // Option B: Replace amounts (do NOT accumulate)
            $entry->amount_received = $amount;
            $entry->gross_total = $amount;

            // Preserve audit trail by appending context
            $entry->reference_notes = trim(
                $notes .
                ($entry->reference_notes ? "\nPrevious submission replaced at " . now()->format('Y-m-d H:i:s') : '')
            );

            // Recompute net revenue from the replaced amount
            $entry->net_revenue = (float)$entry->amount_received
                - (float)$entry->gateway_fee
                - (float)$entry->tax_collected;

            $entry->save();
        } else {
            // Use allowed enum values (Cash, Gcash). GCash submissions are marked as 'Gcash'.
            FinanceEntry::create([
                'booking_id' => $booking->id,
                'customer_name' => $booking->first_name . ' ' . $booking->last_name,
                'gross_total' => $amount,
                'transaction_date' => now()->toDateString(),
                'amount_received' => $amount,
                // Set payment method to the DB-allowed enum. The app treats GCash as Gcash.
                'payment_method' => 'Gcash',
                'gateway_fee' => 0,
                'tax_collected' => 0,
                'reference_notes' => $notes,
                'decline_reason' => null,
                'net_revenue' => $amount,
                'status' => 'Pending Review',
                'created_by' => auth()->id(),
                'reviewed_by' => null,
            ]);
        }

        return redirect('/booking/history')->with('success', 'Payment submitted. Your proof is pending review.');
    }

    private function convertTimeFormat($timeString)
    {
        // Convert "10:30 AM" or "09:20 PM" format to "HH:MM:SS"
        try {
            // Try with leading-zero hour first
            return Carbon::createFromFormat('h:i A', $timeString)->format('H:i:s');
        } catch (\Exception $e) {
            // If parsing fails, try other formats
            try {
                return Carbon::createFromFormat('g:i A', $timeString)->format('H:i:s');
            } catch (\Exception $e) {
                try {
                    return Carbon::createFromFormat('H:i', $timeString)->format('H:i:s');
                } catch (\Exception $e) {
                    // Default fallback
                    return '00:00:00';
                }
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

    /** Ensure mapped integer room IDs align with category prefix buckets. */
    private function roomIdMatchesCategory(int $roomId, string $category): bool
    {
        $category = strtolower($category);
        if ($category === 'individual') {
            return $roomId >= 1001 && $roomId < 2000;
        }
        if ($category === 'common') {
            return $roomId >= 2001 && $roomId < 3000;
        }
        if ($category === 'master') {
            return $roomId >= 3001 && $roomId < 4000;
        }
        return false;
    }
}
