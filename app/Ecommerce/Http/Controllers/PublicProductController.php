<?php

namespace App\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Ecommerce\Models\Product;
use App\Ecommerce\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * Display product catalog page
     */
    public function index(Request $request): View
    {
        $products = $this->productService->getPublicProducts([
            'sort' => $request->get('sort', 'created_at'),
            'order' => $request->get('order', 'desc'),
            'search' => $request->get('search'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
        ]);

        return view('ecommerce.public.catalog.index', [
            'products' => $products,
            'categories' => $this->productService->getCategories(),
            'filters' => $this->productService->getAvailableFilters(),
        ]);
    }

    /**
     * Display single product page
     */
    public function show(string $slug): View
    {
        $product = $this->productService->getPublicProductBySlug($slug);
        
        abort_unless($product, 404);

        $relatedProducts = $this->productService->getRelatedProducts($product, 4);

        return view('ecommerce.public.catalog.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
