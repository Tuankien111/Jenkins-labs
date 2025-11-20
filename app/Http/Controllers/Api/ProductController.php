<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProductController
 *
 * @author AI.Architect
 */
class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        // Rule SQL #4: Shouldn't select * -> Eloquent handles this, but we can be specific
        $products = Product::with('category')->select('id', 'name', 'price', 'category_id')->paginate(10);
        return response()->json($products);
    }

    // ... (Other methods show, store, update, destroy follow similar standard)
}