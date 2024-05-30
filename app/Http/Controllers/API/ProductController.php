<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\Material;
use App\Models\ProductSuggestion;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSuggestionRequest;

class ProductController extends Controller
{
    public function getInstructions(Request $request)
    {
        $data = $request->all();
        $barcode = false;

        // Get barcode from image or string
        $image = $request->file('image');
        if ($image) {
            $imageBarcode = $this->getBarcode($image);
            $imageBarcode = $imageBarcode['response']->barcodes;
            if(is_string($imageBarcode) && $imageBarcode != ''){
                $barcode = $imageBarcode;
            } else {
                return response()->json(['data' => [], 'message' => 'Unable to capture barcode from image, please try again'], 404);
            }
        } else if (isset($data['barcode']) && $data['barcode'] !== ''){
            $barcode = $data['barcode'];
        }

        // Check for a valid Barcode
        if (!$barcode) {
            return response()->json(['data' => [], 'message' => 'No image or barcode was provided'], 404);
        }

        // Search for its instructions in the database
        $product = Product::with('materials')->where('barcode', $barcode)->first();
        if ($product) {
            return response()->json(['data' => ['product' => $product], 'message' => 'Product retrieved successfully'], 200);
        } else {
            return response()->json(['data' => ['barcode' => $barcode], 'message' => 'Product is not in our system'], 404);
        }
    }

    private function getBarcode($image)
    {
        // Create a Guzzle client
        $client = new Client();

        try {
            // Send a POST request to the external API
            $response = $client->post('https://recycleme-haris-ali-ae821b2b.koyeb.app/api/test', [
                'multipart' => [
                    [
                        'name'     => 'image',
                        'contents' => fopen($image->getPathname(), 'r'),
                        'filename' => $image->getClientOriginalName(),
                    ],
                ],
            ]);

            // Get the response from the API
            $responseBody = $response->getBody()->getContents();

            // Return the response from the API
            return ['response' => json_decode($responseBody)];
        } catch (\Exception $e) {
            // Handle errors
            return ['error' => $e->getMessage()];
        }
    }

    public function addSuggestion(ProductSuggestionRequest $request){
        $suggestion = new ProductSuggestion();
        $suggestion->name = $request->name;
        $suggestion->barcode = $request->barcode;
        $suggestion->save();
        return response()->json(['data'=> [], 'message' => "Suggestions added successfully."],200);
    }

    public function import(Request $request)
    {
        $products = $request->all()['products'];
        foreach ($products as $product) {
            $materialIDs = [];
            foreach ($product['material'] as $material) {
                $materialM = Material::where('name', $material)->first();
                if ($materialM) {
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
