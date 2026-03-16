<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $products = Product::with(['category', 'subCategory', 'childCategory'])->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('status', '=', 1, 'and')->get();
        $attributes = Attribute::with(['values' => function ($query) {
            $query->where('status', true)->orderBy('display_order', 'asc');
        }])->where('status', true)->orderBy('group')->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|boolean',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|array',
            'attributes.*.*' => 'integer|exists:attribute_values,id',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        $data['attributes'] = $this->sanitizeAttributes($request->input('attributes', []));

        // Handle Dynamic Multiple Images
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->extension();
                $image->move(public_path('uploads/products'), $imageName);
                $images[] = 'products/' . $imageName;
            }
            $data['images'] = $images;
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', '=', 1, 'and')->get();
        $subCategories = SubCategory::where('category_id', '=', $product->category_id, 'and')->get();
        $childCategories = ChildCategory::where('sub_category_id', '=', $product->sub_category_id, 'and')->get();
        $attributes = Attribute::with(['values' => function ($query) {
            $query->where('status', true)->orderBy('display_order', 'asc');
        }])->where('status', true)->orderBy('group')->orderBy('name')->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'subCategories', 'childCategories', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|boolean',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|array',
            'attributes.*.*' => 'integer|exists:attribute_values,id',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        $data['attributes'] = $this->sanitizeAttributes($request->input('attributes', []));

        // Handle Images
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images) {
                foreach ($product->images as $oldImage) {
                    if (file_exists(public_path('uploads/' . $oldImage))) {
                        unlink(public_path('uploads/' . $oldImage));
                    }
                }
            }
            $images = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->extension();
                $image->move(public_path('uploads/products'), $imageName);
                $images[] = 'products/' . $imageName;
            }
            $data['images'] = $images;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->images) {
            foreach ($product->images as $image) {
                if (file_exists(public_path('uploads/' . $image))) {
                    unlink(public_path('uploads/' . $image));
                }
            }
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function getSubCategories($category_id)
    {
        $subCategories = SubCategory::where('category_id', '=', $category_id, 'and')->where('status', '=', 1, 'and')->get();
        return response()->json($subCategories);
    }

    public function getChildCategories($sub_category_id)
    {
        $childCategories = ChildCategory::where('sub_category_id', '=', $sub_category_id, 'and')->where('status', '=', 1, 'and')->get();
        return response()->json($childCategories);
    }

    private function sanitizeAttributes(array $attributes): array
    {
        $clean = [];

        foreach ($attributes as $attributeId => $values) {
            $valueIds = array_values(array_unique(array_filter(array_map('intval', (array) $values))));
            if (!empty($valueIds)) {
                $clean[(int) $attributeId] = $valueIds;
            }
        }

        return $clean;
    }
}
