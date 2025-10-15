<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    // Show all feedback for Admin Officer with filters and pagination
    public function index(Request $request)
    {
        $query = Feedback::query()->with('user');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by search (user name or comment)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('comments', 'like', "%{$search}%");
            });
        }

        // Pagination
        $feedback = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Summary data for statistics boxes
        $allFeedback = $query->get();
        $totalFeedback = $allFeedback->count();
        $averageRating = $totalFeedback ? round($allFeedback->avg('rating'), 2) : 0;
        $feedbackCountPerType = $allFeedback->groupBy('type')->map->count();

        // Transform for frontend
        $feedback->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'created_at' => $item->created_at,
                'user_name' => $item->user ? $item->user->name : 'Unknown',
                'type' => $item->type,
                'rating' => $item->rating,
                'comments' => $item->comments,
            ];
        });

        return inertia('admin/Feedback', [
            'feedback' => $feedback,
            'totalFeedback' => $totalFeedback,
            'averageRating' => $averageRating,
            'feedbackCountPerType' => $feedbackCountPerType,
        ]);
    }

    // Delete feedback
    public function destroy($id)
    {
        Feedback::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Feedback deleted.');
    }
}