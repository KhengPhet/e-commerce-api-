 public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'required|exists:brands,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'      => 'required|in:active,inactive,out_of_stock',

            // Phone Specs
            'phoneSpec.display'          => 'nullable|string',
            'phoneSpec.cpu'              => 'nullable|string',
            'phoneSpec.ram'              => 'nullable|string',
            'phoneSpec.storage'          => 'nullable|string',
            'phoneSpec.battery'          => 'nullable|string',
            'phoneSpec.camera'           => 'nullable|string',
            'phoneSpec.operating_system' => 'nullable|string',
            'phoneSpec.network'          => 'nullable|string',
            'phoneSpec.warranty'         => 'nullable|string',

            // Laptop Specs
            'laptopSpec.cpu'      => 'nullable|string|max:255',
            'laptopSpec.ram'      => 'nullable|string|max:100',
            'laptopSpec.storage'  => 'nullable|string|max:150',
            'laptopSpec.screen'   => 'nullable|string|max:150',
            'laptopSpec.vga'      => 'nullable|string|max:150',
            'laptopSpec.os'       => 'nullable|string|max:150',
            'laptopSpec.keyboard' => 'nullable|string|max:150',
            'laptopSpec.battery'  => 'nullable|string|max:150',
            'laptopSpec.warranty' => 'nullable|string|max:150',

            // iPad Specs
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

            // Smart Watch Specs
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

        // Image upload
        $uploadPath = public_path('images/products');
        if (!file_exists($uploadPath)) mkdir($uploadPath, 0777, true);
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
            $data['image'] = $imageName;
        }

        // Create product
        $product = Product::create($data);

        // Category-based specs creation
        if ($request->category_id == 1 && $request->has('phoneSpec')) {
            PhoneSpec::create(array_merge(['product_id' => $product->id], $request->phoneSpec));
        }
        if ($request->category_id == 2 && $request->has('laptopSpec')) {
            LaptopSpec::create(array_merge(['product_id' => $product->id], $request->laptopSpec));
        }
        if ($request->category_id == 3 && $request->has('ipadSpec')) {
            \App\Models\IpadSpec::create(array_merge(['product_id' => $product->id], $request->ipadSpec));
        }
        if ($request->category_id == 4 && $request->has('smartWatchSpec')) {
            \App\Models\SmartWatchSpec::create(array_merge(['product_id' => $product->id], $request->smartWatchSpec));
        }

        return response()->json([
            'message' => 'Product created successfully!',
            'product' => $product->load('phoneSpec', 'laptopSpec', 'ipadSpec', 'smartWatchSpec')
        ], 201);
    }