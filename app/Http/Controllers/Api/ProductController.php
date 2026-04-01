<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product; 
use App\Http\Resources\ProductResource; 
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(100);

        return ProductResource::collection($products);
    }

public function show($id)
{
    $product = Product::findOrFail($id);
  return new ProductResource($product);
}
}