<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    public function index()
    {
        $shop = auth()->user()->shop;

        $products = Product::where('shop_id', $shop->id)
            ->with(['category', 'brand', 'primaryPhoto'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Shop/Products/Index', [
            'products' => $products,
            'shop' => $shop,
        ]);
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return Inertia::render('Shop/Products/Create', [
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function store(Request $request)
    {
        $shop = auth()->user()->shop;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'price_per_day' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'material' => 'nullable|string|max:100',
            'condition' => 'nullable|in:new,good,fair',
            'status' => 'nullable|in:draft,active,inactive',
            'is_featured' => 'boolean',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $validated['shop_id'] = $shop->id;
        $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['condition'] = $validated['condition'] ?? 'good';

        $product = Product::create($validated);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $this->fileUploadService->uploadImage($photo, 'products');
                $product->photos()->create([
                    'photo_path' => $path,
                    'alt_text' => $validated['name'],
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('shop.products.index')->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $shop = auth()->user()->shop;

        if ($product->shop_id !== $shop->id) {
            abort(403);
        }

        $product->load('photos');
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return Inertia::render('Shop/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $shop = auth()->user()->shop;

        if ($product->shop_id !== $shop->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'price_per_day' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'material' => 'nullable|string|max:100',
            'condition' => 'nullable|in:new,good,fair',
            'status' => 'nullable|in:draft,active,inactive',
            'is_featured' => 'boolean',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $this->fileUploadService->uploadImage($photo, 'products');
                $product->photos()->create([
                    'photo_path' => $path,
                    'alt_text' => $validated['name'],
                    'is_primary' => $product->photos()->count() === 0 && $index === 0,
                    'sort_order' => $product->photos()->count() + $index,
                ]);
            }
        }

        unset($validated['photos']);
        $product->update($validated);

        return redirect()->route('shop.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $shop = auth()->user()->shop;

        if ($product->shop_id !== $shop->id) {
            abort(403);
        }

        $photoPaths = $product->photos->pluck('photo_path')->toArray();
        $this->fileUploadService->deleteMultipleFiles($photoPaths);
        
        $product->photos()->delete();
        $product->delete();

        return redirect()->route('shop.products.index')->with('success', 'Product deleted successfully');
    }
}
