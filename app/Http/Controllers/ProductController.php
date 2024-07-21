<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()->orderBy('updated_at','desc')->paginate(5);
        return view('product.product_index',[
            'products' => $products
        ]);
    }

    public function show(Product $product)
    {
        return view('product.product_view',[
            'product' => $product
        ]);
    }
}
