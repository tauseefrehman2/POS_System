<?php

namespace App\Http\Controllers;

use App\Models\ProductBrand;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    public function index()
    {
        $brands = ProductBrand::all();
        return response()->json($brands);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:product_brands',
            'remote_id' => 'nullable|string',
            'name_url' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|min:0|max:255',
        ]);

        $brand = ProductBrand::create($request->all());

        return response()->json($brand, 201);
    }

    public function show(ProductBrand $productBrand)
    {
        return response()->json($productBrand);
    }

    public function update(Request $request, ProductBrand $productBrand)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:product_brands,slug,' . $productBrand->id,
            'remote_id' => 'nullable|string',
            'name_url' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|min:0|max:255',
        ]);

        $productBrand->update($request->all());

        return response()->json($productBrand);
    }

    public function destroy(ProductBrand $productBrand)
    {
        $productBrand->delete();
        return response()->json(['message' => 'Product brand deleted']);
    }
}
