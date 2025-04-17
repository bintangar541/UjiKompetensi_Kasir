<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalessController extends Controller
{
    public function index()
    {
        return view('sales.index');// Replace 'products.index' with the path of your view file
    }
}
