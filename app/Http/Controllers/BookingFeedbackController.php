<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingFeedback;

class BookingFeedbackController extends Controller
{
    public function showForm(Booking $booking)
    {
        $feedback = BookingFeedback::where('booking_id', $booking->id)
            ->where('user_id', auth()->id())
            ->first();

        return inertia('BookingFeedbackForm', [
            'booking' => $booking,
            'feedback' => $feedback,
        ]);
    }

    public function store(Request $request, Booking $booking)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // Prevent duplicate
        if (BookingFeedback::where('booking_id', $booking->id)->where('user_id', auth()->id())->exists()) {
            return back()->withErrors(['feedback' => 'You already submitted feedback for this booking.']);
        }

        BookingFeedback::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Feedback submitted!');
    }

    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $feedback = BookingFeedback::where('booking_id', $booking->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $feedback->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Feedback updated!');
    }

    public function destroy(Booking $booking)
    {
        $feedback = BookingFeedback::where('booking_id', $booking->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $feedback->delete();

        return back()->with('success', 'Feedback deleted.');
    }
}