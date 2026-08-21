<?php

namespace App\Http\Controllers;

use App\Models\IpadSpec;
use App\Models\LaptopSpec;
use App\Models\PhoneSpec;
use App\Models\Product;
use App\Models\SmartWatchSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // GET all products
    public function index()
    {
        $products = Product::with('phoneSpec')->get();

        foreach ($products as $product) {
            if ($product->image) {
                $images = explode(',', $product->image);

                $product->image = array_map(function ($img) {
                    return url('images/products/' . $img);
                }, $images);
            } else {
                $product->image = [];
            }
        }

        return response()->json($products);
    }


    // GET single product


    public function show($id)
    {
        $product = Product::with('phoneSpec', 'laptopSpec', 'ipadSpec', 'smartwatchSpec')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->image) {
            $images = explode(',', $product->image);

            $product->image = array_map(function ($img) {
                return url('images/products/' . $img);
            }, $images);
        } else {
            $product->image = [];
        }

        return response()->json($product);
    }



    // POST create product

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'required|exists:brands,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'      => 'required|in:active,inactive,out_of_stock',

            // PHONE SPECS
            'phoneSpec.display'          => 'nullable|string',
            'phoneSpec.cpu'              => 'nullable|string',
            'phoneSpec.ram'              => 'nullable|string',
            'phoneSpec.storage'          => 'nullable|string',
            'phoneSpec.battery'          => 'nullable|string',
            'phoneSpec.camera'           => 'nullable|string',
            'phoneSpec.operating_system' => 'nullable|string',
            'phoneSpec.network'          => 'nullable|string',
            'phoneSpec.warranty'         => 'nullable|string',

            // LAPTOP SPECS
            'laptopSpec.cpu'      => 'nullable|string|max:255',
            'laptopSpec.ram'      => 'nullable|string|max:100',
            'laptopSpec.storage'  => 'nullable|string|max:150',
            'laptopSpec.screen'   => 'nullable|string|max:150',
            'laptopSpec.vga'      => 'nullable|string|max:150',
            'laptopSpec.os'       => 'nullable|string|max:150',
            'laptopSpec.keyboard' => 'nullable|string|max:150',
            'laptopSpec.battery'  => 'nullable|string|max:150',
            'laptopSpec.warranty' => 'nullable|string|max:150',

            // IPAD SPECS
            'ipadSpec.chipset'          => 'nullable|string|max:255',
            'ipadSpec.ram'              => 'nullable|string|max:50',
            'ipadSpec.storage'          => 'nullable|string|max:50',
            'ipadSpec.display_size'     => 'nullable|string|max:50',
            'ipadSpec.battery'          => 'nullable|string|max:50',
            'ipadSpec.camera_rear'      => 'nullable|string|max:100',
            'ipadSpec.operating_system' => 'nullable|string|max:100',
            'ipadSpec.network'          => 'nullable|string|max:50',
            'ipadSpec.accessories'      => 'nullable|string|max:255',
            'ipadSpec.weight'           => 'nullable|string|max:50',
            'ipadSpec.warranty'         => 'nullable|string|max:50',

            // SMARTWATCH SPECS
            'smartWatchSpec.display_type'     => 'nullable|string',
            'smartWatchSpec.display_size'     => 'nullable|string',
            'smartWatchSpec.resolution'       => 'nullable|string',
            'smartWatchSpec.chipset'          => 'nullable|string',
            'smartWatchSpec.ram'              => 'nullable|string',
            'smartWatchSpec.storage'          => 'nullable|string',
            'smartWatchSpec.battery_life'     => 'nullable|string',
            'smartWatchSpec.charging_type'    => 'nullable|string',
            'smartWatchSpec.gps'              => 'nullable|string',
            'smartWatchSpec.water_resistance' => 'nullable|string',
            'smartWatchSpec.sensors'          => 'nullable|string',
            'smartWatchSpec.connectivity'     => 'nullable|string',
            'smartWatchSpec.operating_system' => 'nullable|string',
            'smartWatchSpec.compatibility'    => 'nullable|string',
            'smartWatchSpec.weight'           => 'nullable|string',
            'smartWatchSpec.warranty'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // ===============================
        // MULTIPLE IMAGE UPLOAD
        // ===============================
        $imageNames = [];

        if ($request->hasFile('images')) {
            $uploadPath = public_path('images/products');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            foreach ($request->file('images') as $img) {
                $name = uniqid() . '.' . $img->extension();
                $img->move($uploadPath, $name);
                $imageNames[] = $name;
            }
        }

        $data['image'] = implode(',', $imageNames);

        // CREATE PRODUCT
        $product = Product::create($data);

        
        // CREATE SPECS BY CATEGORY
        $category = \App\Models\Category::find($request->category_id);
        $categoryName = $category ? $category->name : null;

        switch ($categoryName) {
            case 'Phone':
                if ($request->phoneSpec) {
                    $phoneSpecData = json_decode($request->phoneSpec, true);
                    PhoneSpec::create(array_merge(['product_id' => $product->id], $phoneSpecData));
                }
                break;

            case 'Laptop':
                if ($request->laptopSpec) {
                    $laptopSpecData = json_decode($request->laptopSpec, true);
                    LaptopSpec::create(array_merge(['product_id' => $product->id], $laptopSpecData));
                }
                break;

            case 'iPad':
                if ($request->ipadSpec) {
                    $ipadSpecData = json_decode($request->ipadSpec, true);
                    IpadSpec::create(array_merge(['product_id' => $product->id], $ipadSpecData));
                }
                break;

            case 'Smartwatch':
                if ($request->smartWatchSpec) {
                    $smartWatchSpecData = json_decode($request->smartWatchSpec, true);
                    SmartWatchSpec::create(array_merge(['product_id' => $product->id], $smartWatchSpecData));
                }
                break;
        }


        return response()->json([
            'message' => 'Product created successfully!',
            'product' => $product->load('phoneSpec', 'laptopSpec', 'ipadSpec', 'smartwatchSpec')
        ], 201);
    }



    // PUT update product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_images' => 'array',   // array of image names to delete
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // --- HANDLE EXISTING IMAGES ---
        $currentImages = $product->image ? explode(',', $product->image) : [];

        // DELETE selected images
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $img) {
                $path = public_path('images/products/' . $img);
                if (file_exists($path)) unlink($path);

                $currentImages = array_filter($currentImages, function ($item) use ($img) {
                    return $item !== $img;
                });
            }
        }

        // --- ADD NEW IMAGES ---
        if ($request->hasFile('images')) {

            $uploadPath = public_path('images/products');
            if (!file_exists($uploadPath)) mkdir($uploadPath, 0777, true);

            foreach ($request->file('images') as $img) {
                $imageName = uniqid() . '.' . $img->extension();
                $img->move($uploadPath, $imageName);
                $currentImages[] = $imageName;
            }
        }

        // SAVE IMAGES BACK
        $product->image = implode(',', $currentImages);

        // Update other fields
        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully!',
            'product' => $product
        ]);
    }



    // DELETE product
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $uploadPath = public_path('images/products');
        if ($product->image && file_exists($uploadPath . '/' . $product->image)) {
            unlink($uploadPath . '/' . $product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully!']);
    }

    // PATCH change status only
    public function changeStatus(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,out_of_stock',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Product status updated successfully!',
            'product' => $product
        ]);
    }
}
