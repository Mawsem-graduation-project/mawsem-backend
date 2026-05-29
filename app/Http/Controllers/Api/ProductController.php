<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $products = auth()->user()->shop->products;
        return response()->json([
            'products' => ProductResource::collection($products)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'category_name' => 'nullable|string',
            'name' => 'required|string',
            'sku' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $shop = auth()->user()->shop;

        if (!$shop){
            return response(['message'=>'Shop not found'],404);
        }

        $product = $shop->products()->create($validated);

        return response()->json([
            'message' => 'Product added successfully',
            'data' => new ProductResource($product),
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $shop = auth()->user()->shop;

        if ($product->shop_id != $shop->id){
            return response(['message'=>"You can't access this product"],404);
        }

        return response()->json([
            'product' => new ProductResource($product),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {

        if ($product->shop_id !== auth()->user()->shop->shop_id) {
            return response()->json([
                'message' => 'You are not allowed to update this product',
            ], 403);
        }

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'sku'           => 'sometimes|required|string|unique:products,sku,' . $product->id,
            'category_name' => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'unit'          => 'nullable|string|max:50',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'data'    => new ProductResource($product),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {

        if ($product->shop_id !== auth()->user()->shop->shop_id) {
            return response()->json([
                'message' => 'You are not allowed to delete this product',
            ], 403);
        }

        if ($product->sales()->exists()) {
            return response()->json([
                'message' => 'Cannot delete this product because it has sales',
            ], 400);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
