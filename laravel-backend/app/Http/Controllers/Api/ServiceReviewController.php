<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceReview;
use Illuminate\Http\Request;

class ServiceReviewController extends Controller
{
    public function index() {
        return response()->json(ServiceReview::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'service_id' => 'required|exists:public_services,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string',
            'reviewer_name' => 'nullable|string|max:100'
        ]);

        $review = ServiceReview::create($validated);
        return response()->json($review, 201);
    }

    public function show($id) {
        return response()->json(ServiceReview::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $review = ServiceReview::findOrFail($id);

        $validated = $request->validate([
            'service_id' => 'sometimes|required|exists:public_services,id',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'review_text' => 'nullable|string',
            'reviewer_name' => 'nullable|string|max:100'
        ]);

        $review->update($validated);
        return response()->json($review);
    }

    public function destroy($id) {
        $review = ServiceReview::findOrFail($id);
        $review->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
