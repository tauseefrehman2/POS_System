<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand'])->paginate(50);

        return response()->json([
            'status' => true,
            'message' => 'Product list fetched successfully',
            'data' => $products,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:products',
            'sku' => 'required|string|unique:products',
            'quantity' => 'nullable|integer|min:0',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'product_brand_id' => 'nullable|exists:product_brands,id',
            'buying_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'variation_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|integer|min:0|max:255',
            'order' => 'nullable|integer|min:1',
            'can_purchasable' => 'nullable|integer|min:0|max:255',
            'show_stock_out' => 'nullable|integer|min:0|max:255',
            'maximum_purchase_quantity' => 'nullable|integer|min:1',
            'low_stock_quantity_warning' => 'nullable|integer|min:1',
            'weight' => 'nullable|string',
            'refundable' => 'nullable|integer|min:0|max:255',
            'description' => 'nullable|string',
            'shipping_and_return' => 'nullable|string',
            'add_to_flash_sale' => 'nullable|integer|min:0|max:255',
            'discount' => 'nullable|numeric|min:0',
            'offer_start_date' => 'nullable|date',
            'offer_end_date' => 'nullable|date',
            'shipping_type' => 'nullable|integer|min:0|max:255',
            'shipping_cost' => 'nullable|numeric|min:0',
            'is_product_quantity_multiply' => 'nullable|integer|min:0|max:255',
            'stock_quantity' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string',
        ]);

        $product = Product::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['category', 'brand']),
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'status' => true,
            'message' => 'Product fetched successfully',
            'data' => $product->load(['category', 'brand']),
        ], 200);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:products,slug,'.$product->id,
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'quantity' => 'nullable|integer|min:0',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'product_brand_id' => 'nullable|exists:product_brands,id',
            'buying_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'variation_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|integer|min:0|max:255',
            'order' => 'nullable|integer|min:1',
            'can_purchasable' => 'nullable|integer|min:0|max:255',
            'show_stock_out' => 'nullable|integer|min:0|max:255',
            'maximum_purchase_quantity' => 'nullable|integer|min:1',
            'low_stock_quantity_warning' => 'nullable|integer|min:1',
            'weight' => 'nullable|string',
            'refundable' => 'nullable|integer|min:0|max:255',
            'description' => 'nullable|string',
            'shipping_and_return' => 'nullable|string',
            'add_to_flash_sale' => 'nullable|integer|min:0|max:255',
            'discount' => 'nullable|numeric|min:0',
            'offer_start_date' => 'nullable|date',
            'offer_end_date' => 'nullable|date',
            'shipping_type' => 'nullable|integer|min:0|max:255',
            'shipping_cost' => 'nullable|numeric|min:0',
            'is_product_quantity_multiply' => 'nullable|integer|min:0|max:255',
            'stock_quantity' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string',
        ]);

        $product->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load(['category', 'brand']),
        ], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
            'data' => null,
        ], 200);
    }

    public function getQuantity()
    {
        $products = Product::select('id', 'name', 'quantity')->paginate(50);

        return response()->json([
            'status' => true,
            'message' => 'Product quantity list fetched successfully',
            'data' => $products,
        ], 200);
    }

    public function search(Request $request)
    {
        $search = $request->search;

        $products = Product::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('name_ur', 'like', "%$search%")
                ->orWhere('sku', 'like', "%$search%")
                ->orWhere('barcode_id', 'like', "%$search%");
        })
            ->paginate(10);

        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No product found',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product search result',
            'data' => $products,
        ], 200);
    }
}
