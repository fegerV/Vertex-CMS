<?php

namespace App\Ecommerce\Http\Controllers;

use App\Ecommerce\Models\Product;
use App\Ecommerce\Services\ProductService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(Request $request)
    {

        $query = Product::query()->with(['creator', 'updater']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.ecommerce.products.index', compact('products'));
    }

    public function create()
    {

        return view('admin.ecommerce.products.create');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'track_inventory' => 'boolean',
            'status' => 'required|in:draft,active,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
        ]);

        $product = $this->productService->create($validated, $request->user());

        return redirect()->route('admin.ecommerce.products.edit', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {

        $product->load(['creator', 'updater', 'media']);

        return view('admin.ecommerce.products.show', compact('product'));
    }

    public function edit(Product $product)
    {

        $product->load(['media']);

        return view('admin.ecommerce.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'track_inventory' => 'boolean',
            'status' => 'required|in:draft,active,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
        ]);

        $product = $this->productService->update($product, $validated, $request->user());

        return redirect()->route('admin.ecommerce.products.edit', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {

        $this->productService->delete($product, request()->user());

        return redirect()->route('admin.ecommerce.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
