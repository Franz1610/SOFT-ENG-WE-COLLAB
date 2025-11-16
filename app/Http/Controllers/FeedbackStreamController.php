<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicFeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackStreamController extends Controller
{
    /**
     * Return the latest feedback entries for the public live view.
     */
    public function latest(Request $request)
    {
        $limit = (int) $request->query('limit', 20);
        $limit = max(1, min($limit, 50));

        $feedback = Feedback::with('user')
            ->latest()
            ->take($limit)
            ->get();

        return response()->json([
            'data' => PublicFeedbackResource::collection($feedback)->resolve(),
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }
}
