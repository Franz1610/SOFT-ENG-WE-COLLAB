<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceEntry;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * List all payments (pending + verified) with booking and user info.
     */
    public function index(Request $request)
    {
        $status = $request->query('status'); // optional filter

        $query = FinanceEntry::with(['booking.user'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $entries = $query->get()->map(function ($e) {
            return [
                'id' => $e->id,
                'booking_id' => $e->booking_id,
                'customer' => $e->customer_name,
                'email' => optional($e->booking->user)->email,
                'date' => optional($e->transaction_date)->format('Y-m-d'),
                'amount_received' => (float)$e->amount_received,
                'gross_total' => (float)$e->gross_total,
                'reference' => $e->reference_notes,
                'decline_reason' => $e->decline_reason,
                'status' => $e->status,
                'proof_url' => $this->extractProofUrl($e->reference_notes),
                'created_at' => $e->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('admin/ManagePayments', [
            'payments' => $entries,
        ]);
    }

    /**
     * Accept/verify a payment and mark booking as paid for history.
     */
    public function accept($id)
    {
        $entry = FinanceEntry::with('booking')->findOrFail($id);
        $entry->status = 'Verified';
        $entry->reviewed_by = Auth::id();
        $entry->save();

        // Ensure the linked booking becomes actionable in Room Management.
        // If the booking is still pending, mark it as confirmed so it appears
        // as Reserved/Occupied according to its time window.
        if ($entry->booking && $entry->booking->status === 'pending') {
            $entry->booking->update(['status' => 'confirmed']);
        }

        return redirect()->back()->with('success', 'Payment verified.');
    }

    /**
     * Decline a payment by setting to Unprocessed (fallback state) and leaving a note.
     */
    public function decline(Request $request, $id)
    {
        $entry = FinanceEntry::findOrFail($id);
        $entry->status = 'Unprocessed';
        $reason = trim((string)$request->input('reason', 'No reason provided'));
        // store structured reason and keep legacy notes for traceability
        $entry->decline_reason = $reason;
        $entry->declined_at = now();
        $note = trim(($entry->reference_notes ?: '') . "\nDeclined by admin: " . $reason);
        $entry->reference_notes = $note;
        $entry->reviewed_by = Auth::id();
        $entry->save();

        return redirect()->back()->with('success', 'Payment marked as declined.');
    }

    private function extractProofUrl(?string $notes): ?string
    {
        // If submitPayment appended "storage/…" path into notes, try to extract it.
        if (!$notes) return null;
        if (preg_match('/storage\/[\w\-\/\.]+/i', $notes, $m)) {
            return '/' . ltrim($m[0], '/');
        }
        return null;
    }

    /**
     * Stream the proof image securely ensuring only authorized admins can view.
     */
    public function proof(FinanceEntry $financeEntry)
    {
        // Basic authorization: ensure user is admin (adjust per your policy/permissions)
        $user = Auth::user();
        if (!$user || !(bool)($user->is_admin ?? false)) {
            abort(403);
        }

        $notes = $financeEntry->reference_notes;
        $relative = null;
        if ($notes && preg_match('/payment_proofs\/[\w\-]+\.\w{2,4}/i', $notes, $m)) {
            // File stored under storage/app/public/payment_proofs usually; use public disk
            $relative = $m[0];
        }

        if (!$relative || !Storage::disk('public')->exists($relative)) {
            abort(404, 'Proof not found');
        }

        $mime = Storage::disk('public')->mimeType($relative) ?: 'image/jpeg';

        return new StreamedResponse(function () use ($relative) {
            $stream = Storage::disk('public')->readStream($relative);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
