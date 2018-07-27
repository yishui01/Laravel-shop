<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::where('on_sale', true)->paginate(16);
        return view('products.index', ['products'=>$products]);
    }
}
