<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getInstructions(Request $request){
        $data = $request->all();

        // In case of an Image

        // In case of a code
        $barcode = $data['barcode'];

        $product = Product::with('materials')->where('barcode', $barcode)->first();
        if($product){
            return response()->json(['data' => ['product' => $product], 'message' => 'Product retrieved successfully'], 200);
        } else {
            return response()->json(['data' => [], 'message' => 'Product is not in our system'], 404);
        }

    }
    public function import(Request $request){
        $products = $request->all()['products'];
        foreach($products as $product){
            $materialIDs = [];
            foreach($product['material'] as $material){
                $materialM = Material::where('name', $material)->first();
                if($materialM){
                    $materialIDs[] = $materialM->id;
                } else {
                    $materialM = new Material();
                    $materialM->name = $material;
                    $materialM->save();
                    $materialM->refresh();
                    $materialIDs[] = $materialM->id;
                }

            }

            $productM = new Product();
            $productM->name = $product['name'];
            $productM->barcode = $product['barcode'];
            $productM->type = $product['type'];
            $productM->instructions = $product['instructions'];
            $productM->save();
            $productM = Product::where('barcode', $product['barcode'])->first();
            $productM->materials()->sync($materialIDs);
        }
        return response()->json(['message' => 'success']);
    }
}
