<?php

namespace App\Http\Controllers;

use App\Models\AcmSmComment;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitSmCommentController extends Controller
{
    public function store(Request $request, int $visitId)
    {
        Visit::findOrFail($visitId); // ensure exists

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        AcmSmComment::create([
            'visit_id'  => $visitId,
            'emp_id'    => $user->emp_id,
            'comment'   => $validated['comment'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Comment recorded.']);
        }

        return back()->with('success', 'Comment recorded.');
    }
}
