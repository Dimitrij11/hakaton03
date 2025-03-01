<?php

namespace App\Http\Controllers\API;

use App\Models\Review;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends BaseController
{
    /**
     * Display reviews for a specific course.
     *
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCourse($courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return $this->sendError('Course not found.');
        }

        $reviews = Review::with('user')->where('course_id', $courseId)->get();

        return $this->sendResponse($reviews, 'Reviews retrieved successfully.');
    }

    /**
     * Store a newly created review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        // Check if the user has already reviewed this course
        $existingReview = Review::where('user_id', Auth::id())
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingReview) {
            return $this->sendError(
                'Review already exists.',
                ['error' => 'You have already reviewed this course. Please update your existing review.'],
                422
            );
        }

        $review = new Review();
        $review->user_id = Auth::id();
        $review->course_id = $request->course_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return $this->sendResponse($review, 'Review created successfully.');
    }

    /**
     * Update the specified review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (is_null($review)) {
            return $this->sendError('Review not found.');
        }

        // Check if the user is the owner of the review
        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return $this->sendError('Unauthorized', ['error' => 'You can only update your own reviews'], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        if ($request->has('rating')) {
            $review->rating = $request->rating;
        }

        if ($request->has('comment')) {
            $review->comment = $request->comment;
        }

        $review->save();

        return $this->sendResponse($review, 'Review updated successfully.');
    }

    /**
     * Remove the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (is_null($review)) {
            return $this->sendError('Review not found.');
        }

        // Check if the user is the owner of the review or an admin
        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return $this->sendError('Unauthorized', ['error' => 'You can only delete your own reviews'], 403);
        }

        $review->delete();

        return $this->sendResponse([], 'Review deleted successfully.');
    }
}
