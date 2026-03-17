<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:product_categories',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|min:0|max:255',
        ]);

        $category = ProductCategory::create($request->all());

        return response()->json($category, 201);
    }

    public function show(ProductCategory $productCategory)
    {
        return response()->json($productCategory);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:product_categories,slug,' . $productCategory->id,
            'description' => 'nullable|string',
            'status' => 'nullable|integer|min:0|max:255',
        ]);

        $productCategory->update($request->all());

        return response()->json($productCategory);
    }

    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();
        return response()->json(['message' => 'Product category deleted']);
    }
}
