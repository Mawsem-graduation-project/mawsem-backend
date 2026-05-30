<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request()->get('per_page', 15);
        $sales = auth()->user()->shop->sales()->paginate($perPage);
        return SaleResource::collection($sales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        $shop = auth()->user()->shop;
        if ($shop->id !== $sale->shop_id){
            return response()->json([
                'message' => 'You can\'t delete this sale'
            ],403);
        }

        if ($sale->delete()){
            return response()->json([
                'message' => 'Sale deleted successfully',
            ],200);
        }

        return response()->json([
            'message' => 'Something went wrong, could not delete the sale'
        ], 500);
    }

    public function destroyAll()
    {
        $shop = auth()->user()->shop;

        if ($shop->sales()->count() === 0) {
            return response()->json([
                'message' => 'There are no sales to delete'
            ], 404);
        }

        if ($shop->sales()->delete() > 0){
            return response()->json([
                'message' => 'Sales deleted successfully'
            ],200);
        }

        return response()->json([
            'message' => 'Something went wrong, could not delete the sales'
        ], 500);
    }
}
