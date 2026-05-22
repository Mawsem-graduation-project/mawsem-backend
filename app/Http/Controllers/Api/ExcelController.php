<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelController extends Controller
{
    //
    public function exportSalesToCSV(Request $request){
        $fileName = 'historical_sales'.date('Y-m-d').'.csv';
        $user = auth()->user();

        $response = new StreamedResponse(function () use ($user) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array('sale_date', 'product_sku', 'quantity'));

            Sale::where('shop_id',$user->shop_id)->with(['product'])->chunk(100, function($sales) use($handle) {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->sale_date,
                        $sale->product->sku,
//                        $sale->product->name,
//                        $sale->product->category?->name,
                        $sale->quantity
                    ]);
                }
            });
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        return $response;
    }

    public function importSales(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);
        $shop = auth()->user()->shop;

        if(!$shop){
            return response()->json(['error' => 'shop not found'], 404);
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        DB::beginTransaction();

        try{
            $handle = fopen($filePath, 'r');

            $productCreated = 0;
            $saleCreated = 0;

            $headers = fgetcsv($handle, 1000, ',');
            if($handle){
                while(($row = fgetcsv($handle, 1000, ',')) !== false){
                    $saleDate = $row[0];
                    $productSku = $row[1];
                    $productName = $row[2];
                    $quantity = (int)$row[3];
                    $category = $row[4];

                    if (empty($productName) || empty($category)) {
                        continue;
                    }

                    $product = Product::where('shop_id', $shop->id)->where('sku', $productSku)->first();

                    if (!$product) {
                        $product = Product::create([
                            'shop_id' => $shop->id,
                            'sku' => $productSku,
                            'name' => $productName,
                            'category_name' => $category,
                        ]);
                        $productCreated++;
                    }

                    Sale::create([
                        'shop_id' => $shop->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'sale_date' => Carbon::parse($saleDate)->toDateTimeString(),
                    ]);
                    $saleCreated++;
                }
                fclose($handle);
            }

            DB::commit();

            return response()->json([
                'success' => $productCreated.' products and sales have been imported.',
                'stats' => [
                    'product_created' => $productCreated,
                    'sale_created' => $saleCreated
                ]
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'message' => "Error while processing your file",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
