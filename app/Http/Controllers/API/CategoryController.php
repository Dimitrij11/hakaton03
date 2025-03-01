<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::all();

        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Store a newly created category in storage.
     * Only admin can create categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return $this->sendError('Unauthorized', ['error' => 'Only administrators can create categories'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $category = Category::create($request->all());

        return $this->sendResponse($category, 'Category created successfully.');
    }

    /**
     * Display the specified category with its courses.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = Category::with('courses')->find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        return $this->sendResponse($category, 'Category retrieved successfully.');
    }

    /**
     * Update the specified category in storage.
     * Only admin can update categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return $this->sendError('Unauthorized', ['error' => 'Only administrators can update categories'], 403);
        }

        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $category->update($request->all());

        return $this->sendResponse($category, 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     * Only admin can delete categories.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return $this->sendError('Unauthorized', ['error' => 'Only administrators can delete categories'], 403);
        }

        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        // Check if the category has courses
        if ($category->courses()->count() > 0) {
            return $this->sendError('Cannot delete category', ['error' => 'This category has courses associated with it'], 422);
        }

        $category->delete();

        return $this->sendResponse([], 'Category deleted successfully.');
    }
}
