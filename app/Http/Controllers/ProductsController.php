<?php

namespace App\Http\Controllers;
use App\Models\products;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        return view('product.index');// Replace 'products.index' with the path of your view file
    }
}
