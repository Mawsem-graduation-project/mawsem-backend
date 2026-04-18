<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\Shop;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $shop = auth()->user()->shop;
        $inventory = Inventory::with('product','shop')->where('shop_id',$shop->id)->get();

        return InventoryResource::collection($inventory);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'current_stock' => 'required|integer',
            'minimum_stock' => 'required|integer',
        ]);

        $shop = auth()->user()->shop;
        $validated['shop_id'] = $shop->id;

        $inventory = Inventory::create($validated);

        return response()->json([
            'message' => 'Product added to inventory',
            'data' => $inventory,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        //
        $shop = auth()->user()->shop;
        if (!$shop || $shop->id !== $inventory->shop_id){
            return response()->json([
                'message' => 'You cannot show this inventory',
            ]);
        }
        return new InventoryResource($inventory->load('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        //
        $validated = $request->validate([
            'product_id' => 'exists:products,id',
            'current_stock' => 'integer',
            'minimum_stock' => 'integer',
        ]);

        $shop = auth()->user()->shop;
        if (!$shop || $shop->id !== $inventory->shop_id){
            return response()->json([
                'message' => 'You cannot edit this inventory',
            ],403);
        }

        $inventory->update($validated);
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $inventory,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        //
        $shop = auth()->user()->shop;

        if (!$shop || $shop->id !== $inventory->shop_id){
            return response()->json([
                'message' => 'You cannot delete this inventory',
            ],403);
        }
        $inventory->delete();
        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
