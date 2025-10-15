<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'required|string',
        ]);

        Feedback::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'rating' => $request->rating,
            'comments' => $request->comments,
        ]);

        return redirect()->back()->with('success', 'Feedback submitted!');
    }
}
