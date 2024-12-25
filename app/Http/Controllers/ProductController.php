<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('images')->paginate(10); // Load images relationship
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array', // images bây giờ là nullable
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|integer',
            'alt' => 'nullable|array',
            'alt.*' => 'nullable|string'
        ]);
        dump($request);
        try {
            DB::beginTransaction();

            $product = Product::create($request->except('images', 'thumbnail', 'alt'));

            if ($request->hasFile('images')) { // Chỉ xử lý ảnh nếu có file được upload
                $images = $request->file('images');
                $thumbnail = $request->input('thumbnail', 0);
                $alts = $request->input('alt', []);

                foreach ($images as $key => $image) {
                    $path = Storage::disk('s3')->putFile('products', $image, 'public');
                    if ($path === false) {
                        DB::rollBack();
                        Log::error('Failed to upload image to S3');
                        return response()->json(['message' => 'Failed to upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => Storage::disk('s3')->url($path),
                        'alt' => $alts[$key] ?? null,
                        'is_thumbnail' => $key == $thumbnail,
                    ]);
                }
            }

            DB::commit();

            return response()->json(Product::with('images')->find($product->id), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Product $product)
    {
        $product->load('images'); // Load images relationship
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|integer',
            'alt' => 'nullable|array', // Validation cho mảng alt
            'alt.*' => 'nullable|string' // Validation cho từng alt trong mảng
        ]);

        try {
            DB::beginTransaction();

            $product->update($request->except(['images', 'thumbnail', 'alt']));

            if ($request->hasFile('images')) {
                // Xóa ảnh cũ
                foreach ($product->images as $oldImage) {
                    Storage::disk('s3')->delete(str_replace(env('AWS_URL').'/', '', $oldImage->path));
                    $oldImage->delete();
                }

                $images = $request->file('images');
                $thumbnail = $request->input('thumbnail', 0);
                $alts = $request->input('alt', []);

                foreach ($images as $key => $image) {
                     $path = Storage::disk('s3')->putFile('products', $image, 'public');
                     if ($path === false) {
                        DB::rollBack();
                        Log::error('Failed to upload image to S3');
                        return response()->json(['message' => 'Failed to upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => Storage::disk('s3')->url($path),
                        'alt' => $alts[$key] ?? null,
                        'is_thumbnail' => $key == $thumbnail,
                    ]);
                }
            }

            DB::commit();
            return response()->json(Product::with('images')->find($product->id)); // Trả về product với images
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();
                foreach ($product->images as $oldImage) {
                    Storage::disk('s3')->delete(str_replace(env('AWS_URL').'/', '', $oldImage->path));
                    $oldImage->delete();
                }
            $product->delete();
            DB::commit();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['message' => 'Failed to delete product', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}