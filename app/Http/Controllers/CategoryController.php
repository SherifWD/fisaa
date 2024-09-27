<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CategoryController extends Controller
{
    use HelpersTrait;

    public function getCategories()
    {
        $categories = Category::all();

        return $this->returnData('categories', $categories, 'Categories retrieved successfully');
    }

    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'short_title' => 'nullable|string',
            'is_discount' => 'boolean',
            'discount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $category = Category::create($request->all());

        return $this->returnData('category', $category, 'Category created successfully');
    }
}