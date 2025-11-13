<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\FinanceEntry;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        // Get filter values from request
        $type = $request->input('type');
        $category = $request->input('category');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Get all finance entries with their relationships
        $financeEntries = FinanceEntry::with(['booking.user', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Get all general transactions
        $generalTransactions = Transaction::with('user')
            ->orderBy('date', 'desc')
            ->get();

        // Combine and transform all transactions. Include a sort key that uses precise creation time
        // so entries on the same date are still ordered newest-first.
        $financeTransactions = $financeEntries->map(function ($entry) {
            return [
                'id' => 'finance_' . $entry->id,
                'date' => $entry->transaction_date->format('Y-m-d'),
                'description' => "Payment for booking #{$entry->booking_id} - {$entry->customer_name}",
                'type' => 'income',
                'amount' => $entry->amount_received,
                // Always show booking-linked payments under the unified category used by filters/UI
                'category' => 'Booking Payment',
                'payment_method' => $entry->payment_method,
                'user' => $entry->creator,
                'reference' => $entry->reference_notes,
                'source' => 'finance_entry',
                'sort_key' => ($entry->transaction_date?->format('Y-m-d') ?? '0000-00-00') . ' ' . ($entry->created_at?->format('H:i:s') ?? '00:00:00'),
            ];
        });

        $generalTransactionsMapped = $generalTransactions->map(function ($transaction) {
            return [
                'id' => 'transaction_' . $transaction->id,
                'date' => $transaction->date->format('Y-m-d'),
                'description' => $transaction->description,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'category' => $transaction->category,
                'payment_method' => $transaction->payment_method,
                'user' => $transaction->user,
                'reference' => $transaction->reference,
                'source' => 'general_transaction',
                'sort_key' => ($transaction->date?->format('Y-m-d') ?? '0000-00-00') . ' ' . ($transaction->created_at?->format('H:i:s') ?? '00:00:00'),
            ];
        });

        // Merge all transactions and sort by the precise sort key (date + created time)
        $allTransactions = $financeTransactions->concat($generalTransactionsMapped)
            ->sortByDesc('sort_key')
            ->values();

        // --- Apply filters ---
        $filteredTransactions = $allTransactions->filter(function ($t) use ($type, $category, $start_date, $end_date) {
            $match = true;
            if ($type) {
                $match = $match && ($t['type'] === $type);
            }
            if ($category) {
                $match = $match && ($t['category'] === $category);
            }
            if ($start_date) {
                $match = $match && ($t['date'] >= $start_date);
            }
            if ($end_date) {
                $match = $match && ($t['date'] <= $end_date);
            }
            return $match;
        })->values();

        // Calculate summary statistics for filtered transactions
        $totalIncome = $filteredTransactions->where('type', 'income')->sum('amount');
        $totalExpenses = $filteredTransactions->where('type', 'expense')->sum('amount');
        $netRevenue = $filteredTransactions->where('source', 'finance_entry')->sum('amount');
        
        $summary = [
            'income' => $totalIncome,
            'expense' => $totalExpenses,
            'net' => $totalIncome - $totalExpenses,
            'revenue' => $netRevenue,
        ];

        // Define categories
        $incomeCategories = ['Booking Payment', 'Additional Service', 'Other Income'];
        $expenseCategories = ['Maintenance', 'Utilities', 'Supplies', 'Staff Salary'];
        $miscIncomeCategories = ['Ad-hoc Income']; // Categories specific to Misc. Income type

        return inertia('admin/Finance', [
            'transactions' => $filteredTransactions,
            'summary' => $summary,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'miscIncomeCategories' => $miscIncomeCategories,
        ]);
    }

    public function create()
    {
        $bookings = Booking::whereDoesntHave('financeEntry')->get();
        return inertia('admin/FinanceCreate', ['bookings' => $bookings]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id|unique:finance_entries,booking_id',
            'transaction_date' => 'required|date|before_or_equal:today',
            'amount_received' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Credit Card,Bank Transfer,Cash,Gift Card,Other',
            'gateway_fee' => 'nullable|numeric|min:0',
            'tax_collected' => 'nullable|numeric|min:0',
            'reference_notes' => 'required|string',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        $net_revenue = $request->amount_received - ($request->gateway_fee ?? 0) - ($request->tax_collected ?? 0);

        FinanceEntry::create([
            'booking_id' => $booking->id,
            'customer_name' => $booking->user->name,
            'gross_total' => $booking->total_price,
            'transaction_date' => $request->transaction_date,
            'amount_received' => $request->amount_received,
            'payment_method' => $request->payment_method,
            'gateway_fee' => $request->gateway_fee ?? 0,
            'tax_collected' => $request->tax_collected ?? 0,
            'reference_notes' => $request->reference_notes,
            'net_revenue' => $net_revenue,
            'status' => 'Pending Review',   
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.finance.index')->with('success', 'Finance entry created.');
    }

    public function verify($id)
    {
        $entry = FinanceEntry::findOrFail($id);
        $entry->status = 'Verified';
        $entry->reviewed_by = Auth::id();
        $entry->save();

        return redirect()->back()->with('success', 'Entry verified.');
    }

    // Transaction management methods
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'type' => 'required|in:income,expense,Misc. Income',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string|max:500',
        ]);

        Transaction::create([
            'date' => $request->date,
            'description' => $request->description,
            'type' => $request->type,
            'amount' => $request->amount,
            'category' => $request->category,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'added_by' => Auth::id(),
        ]);

        return redirect()->route('admin.finance.index')->with('success', 'Transaction added successfully.');
    }

    public function updateTransaction(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'type' => 'required|in:income,expense,Misc. Income',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string|max:500',
        ]);

        // Extract the actual transaction ID (remove 'transaction_' prefix if present)
        $transactionId = str_replace('transaction_', '', $id);
        
        $transaction = Transaction::findOrFail($transactionId);
        
        $transaction->update([
            'date' => $request->date,
            'description' => $request->description,
            'type' => $request->type,
            'amount' => $request->amount,
            'category' => $request->category,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
        ]);

        return redirect()->route('admin.finance.index')->with('success', 'Transaction updated successfully.');
    }

    public function deleteTransaction($id)
    {
        // Extract the actual transaction ID (remove 'transaction_' prefix if present)
        $transactionId = str_replace('transaction_', '', $id);
        
        $transaction = Transaction::findOrFail($transactionId);
        $transaction->delete();

        return redirect()->route('admin.finance.index')->with('success', 'Transaction deleted successfully.');
    }
}