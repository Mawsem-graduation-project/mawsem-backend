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

    public function forecast(Product $product)
    {
        set_time_limit(0);

        $shop = auth()->user()->shop;
        if (!$shop || $product->shop_id != $shop->id){
            return response(['message' => 'Shop not found'], 404);
        }

        // 1. جلب المبيعات وبناء الـ CSV كالمعتاد
        $salesData = Sale::where('shop_id', $shop->id)
            ->whereHas('product', function($q) use ($product) {
                $q->where('products.sku', $product->sku);
            })
            ->with('product:id,sku')
            ->orderBy('sale_date', 'asc')
            ->get();

        // نتحقق من وجود سجلات كافية في قاعدة البيانات (354 يوماً هجرياً)
        if ($salesData->count() < 354) {
            return response()->json(['status' => 'error', 'message' => 'البيانات التاريخية غير كافية، يجب توفر مبيعات 354 يوماً على الأقل.'], 400);
        }

        $csvHeader = "sale_date,product_sku,quantity\n";
        $csvRows = "";
        foreach ($salesData as $sale) {
            $productSku = $sale->product ? $sale->product->sku : $product->sku;
            $csvRows .= "{$sale->sale_date},{$productSku},{$sale->quantity}\n";
        }

        try {
            $csvContent = $csvHeader . $csvRows;

            // 2. إرسال الطلب إلى بايثون كـ Multipart File بدلاً من حقل نصي عادية
            $response = Http::timeout(120)
                ->attach('file', $csvContent, 'sales_data.csv') // إرسال الملف في الذاكرة باسم 'file'
                ->post('https://mawsemaissad.onrender.com/forecast', [
                    'predictionTime' => 360 // المتغيرات الأخرى ترسل كـ Form Data
                ]);

            if ($response->successful()) {
                $apiResult = $response->json();

                // 3. جلب مصفوفة الشهور الجاهزة من بايثون والمقسمة هجرياً بأم القرى
                $forecast = $apiResult['months'] ?? [];

                return response()->json([
                    'product'  => new ProductResource($product),
                    'forecast' => $forecast
                ]);
            }

            // في حال فشل بايثون، نطبع الرد الفعلي في السجلات لتسهيل تتبع الأخطاء
            Log::error('FastAPI Error: ' . $response->body());
            return response()->json([
                'status' => 'error',
                'message' => 'AI failure',
                'details' => $response->json() // يظهر لك تفاصيل الخطأ القادم من بايثون مباشرة في الـ Postman
            ], 400);

        } catch (\Exception $e) {
            Log::critical('FastAPI Connection Failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {

        if ($product->shop_id !== auth()->user()->shop_id) {
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

        if ($product->shop_id !== auth()->user()->shop_id) {
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
