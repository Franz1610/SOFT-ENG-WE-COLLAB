<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user');

        // Filters
        if ($request->type) $query->where('type', $request->type);
        if ($request->category) $query->where('category', $request->category);
        if ($request->start_date) $query->where('date', '>=', $request->start_date);
        if ($request->end_date) $query->where('date', '<=', $request->end_date);

        $transactions = $query->orderBy('date', 'desc')->get();

        // Summary
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;
        $revenue = $transactions->where('type', 'income')->where('category', 'Booking Payment')->sum('amount');

        // Predefined categories
        $incomeCategories = ['Booking Payment', 'Additional Service', 'Other Income'];
        $expenseCategories = ['Maintenance', 'Utilities', 'Supplies', 'Staff Salary'];

        return inertia('admin/Finance', [
            'transactions' => $transactions,
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $netBalance,
                'revenue' => $revenue,
            ],
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'type' => 'required|in:income,expense,Misc. Income',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'reference' => 'nullable|string',
        ]);

        Transaction::create([
            'date' => $request->date,
            'description' => $request->description,
            'type' => $request->type,
            'amount' => $request->amount,
            'category' => $request->category,
            'reference' => $request->reference,
            'added_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Transaction added.');
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'type' => 'required|in:income,expense,Misc. Income',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'reference' => 'nullable|string',
        ]);

        $transaction->update($request->all());

        return redirect()->back()->with('success', 'Transaction updated.');
    }

    public function destroy($id)
    {
        Transaction::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Transaction deleted.');
    }
}