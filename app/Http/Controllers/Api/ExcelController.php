<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelController extends Controller
{
    //
    public function exportSalesToCSV(Request $request){
        $fileName = 'historical_sales'.date('Y-m-d').'.csv';
        $user = auth()->user();

        $response = new StreamedResponse(function () use ($user) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array('sale_date', 'product_sku', 'product_name', 'category', 'quantity'));

            Sale::where('shop_id',$user->shop_id)->with(['product'])->chunk(100, function($sales) use($handle) {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->sale_date,
                        $sale->product->sku,
                        $sale->product->name,
                        $sale->product->category?->name,
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
}
