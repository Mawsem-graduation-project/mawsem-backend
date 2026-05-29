<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    //
    public function summary()
    {
        set_time_limit(0);

        $shop = auth()->user()->shop;
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        // 1. Fetch ALL sales for the current shop ordered by date
        $salesData = Sale::where('shop_id', $shop->id)
            ->with('product:id,sku')
            ->orderBy('sale_date', 'asc')
            ->get();

        if ($salesData->count() < 12) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient data for dashboard analytics.'], 400);
        }

        // 2. Build CSV contents for all products
        $csvHeader = "sale_date,product_sku,quantity\n";
        $csvRows = "";
        foreach ($salesData as $sale) {
            $productSku = $sale->product ? $sale->product->sku : 'UNKNOWN';
            $csvRows .= "{$sale->sale_date},{$productSku},{$sale->quantity}\n";
        }
        $csvContent = $csvHeader . $csvRows;

        try {
            // 3. Send multipart request to the new /dashboard FastAPI endpoint
            $response = Http::timeout(120)
                ->attach('file', $csvContent, 'all_sales_data.csv')
                ->post('https://mawsemaissad.onrender.com/dashboard');

            if ($response->successful()) {
                $res = $response->json();

                $product_SKU = $res['kpis']['top_performing_product']['sku'];
                $res['kpis']['top_performing_product'] = new ProductResource(Product::where('sku', $product_SKU)->first());

                return response()->json($res);
            }

            Log::error('FastAPI Dashboard Error: ' . $response->body());
            return response()->json(['status' => 'error', 'message' => 'AI Analytics failure'], 400);

        } catch (\Exception $e) {
            Log::critical('FastAPI Connection Failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }
}
