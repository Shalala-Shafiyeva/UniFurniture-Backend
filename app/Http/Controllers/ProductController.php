<?php

namespace App\Http\Controllers;

use App\Models\Charasteristic;
use App\Models\Color;
use App\Models\ColorImage;
use App\Models\Product;
use App\Models\ProductView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category', 'type', 'characteristics', 'colors')->get();
        if ($products->count() == 0) {
            return response()->json([
                "data" => [],
                "success" => false
            ], 404);
        }
        return response()->json([
            "data" => $products,
            "success" => true
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Request Data:', $request->all());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'full_title' => 'required|string|max:255',
            'text' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            'video' => 'required|file|mimes:mp4,avi,mov',
            'stock' => 'required|numeric|min:0',
            'garranty' => 'required|numeric|max:100|min:0',
            'shipping' => 'required|numeric|min:1|max:100',
            'price' => 'required|numeric',
            "discount" => 'nullable|numeric|min:0|max:100',
            'type_id' => 'required|exists:types,id',
            'category_id' => 'required|exists:categories,id',
            // 'characteristics' => 'required|array',
            'characteristics.*.characteristic' => 'required|string',
            'colors' => 'required|array',
            'colors.*.colorName' => 'required|string',
            'colors.*.images' => 'required|array',
            'colors.*.images.*' => 'required|file|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products/images', 'public');
        }
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('products/videos', 'public');
        }

        $product = Product::create(array_merge($request->all(), [
            'image' => $imagePath,
            'video' => $videoPath,
        ]));

        $characteristics = json_decode($request->input('characteristics'), true);
        if (!empty($characteristics)) {
            foreach ($characteristics as $char) {
                Charasteristic::create([
                    'product_id' => $product->id,
                    'characteristic' => $char,
                ]);
            }
        }

        foreach ($validatedData['colors'] as $colorData) {
            $color = Color::create([
                'name' => $colorData['colorName'],
                'product_id' => $product->id,
            ]);

            foreach ($colorData['images'] as $image) {
                $colorImagePath = $image->store('color_images', 'public');
                ColorImage::create([
                    'color_id' => $color->id,
                    'image' => $colorImagePath,
                ]);
            }
        }

        return response()->json(['success' => true, 'product' => $product]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, $id)
    {
        $product = Product::with('category', 'type', 'characteristics', 'colors.color_images')->find($id);
        if ($product) {
            return response()->json([
                "data" => $product,
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "Product not found",
                "success" => false
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('Request Data:', $request->all());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'full_title' => 'required|string|max:255',
            'text' => 'required|string',
            'description' => 'required|string',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'video' => 'nullable|file|mimes:mp4,avi,mov|max:2048',
            'stock' => 'required|numeric',
            'garranty' => 'required|numeric',
            'shipping' => 'required|numeric',
            'price' => 'required|numeric',
            'type_id' => 'required|exists:types,id',
            'category_id' => 'required|exists:categories,id',
            // 'characteristics' => 'required|array',
            'characteristics.*.characteristic' => 'required|string',
            'colors' => 'required|array',
            'colors.*.colorName' => 'required|string',
            'colors.*.images' => 'required|array',
            'colors.*.images.*' => 'required|file|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products/images', 'public')
            : $request->input('old_image');

        // Обработка загружаемого видео или использование старого
        $videoPath = $request->hasFile('video')
            ? $request->file('video')->store('products/videos', 'public')
            : $request->input('old_video');

        $product = Product::find($id);

        if ($product) {
            $product->update(array_merge($request->all(), [
                'image' => $imagePath,
                'video' => $videoPath,
            ]));
        } else {
            return response()->json(['error' => 'Product not found', 'success' => false], 404);
        }

        $characteristics = json_decode($request->input('characteristics'), true);
        if (!empty($characteristics)) {
            foreach ($characteristics as $index => $charData) {
                $characteristic = Charasteristic::updateOrCreate(
                    ['id' => $charData['id'], 'product_id' => $product->id],
                    ['description' => $charData['description']]
                );
            }
        }

        foreach ($validatedData['colors'] as $colorData) {
            $color = Color::updateOrCreate(
                ['product_id' => $product->id, 'name' => $colorData['colorName']],
                ['name' => $colorData['colorName']]
            );

            foreach ($colorData['images'] as $image) {
                $colorImagePath = $image->store('color_images', 'public');
                ColorImage::updateOrCreate(
                    ['color_id' => $color->id, 'image' => $colorImagePath],
                    ['image' => $colorImagePath]
                );
            }
        }

        return response()->json(['success' => true, 'product' => $product]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $product = Product::find($id);
        if ($product->delete()) {
            return response()->json([
                "message" => "Product deleted successfully",
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "Product not deleted",
                "success" => false
            ], 500);
        }
    }

    public function publish($id)
    {
        $product = Product::find($id);
        $product->is_publish = true;
        if ($product->save()) {
            return response()->json([
                "message" => "Product published successfully",
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "Product not published",
                "success" => false
            ], 500);
        }
    }

    public function unpublish($id)
    {
        $product = Product::find($id);
        $product->is_publish = false;
        if ($product->save()) {
            return response()->json([
                "message" => "Product unpublished successfully",
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "Product not unpublished",
                "success" => false
            ], 500);
        }
    }

    public function similarProducts($id)
    {
        $product = Product::with('category', 'type', 'characteristics', 'colors')->find($id);
        $similarProducts = Product::with('category', 'type', 'characteristics', 'colors')
            ->where('category_id', $product->category_id)
            ->where('type_id', $product->type_id)
            ->where('id', '!=', $product->id)
            ->get();

        if ($similarProducts->isEmpty()) {
            return response()->json([
                "message" => "No similar products found",
                "success" => false
            ]);
        }
        return response()->json([
            "data" => $similarProducts,
            "success" => true
        ], 200);
    }

    public function publishedProducts()
    {
        $products = Product::with('category', 'type', 'characteristics', 'colors.color_images')->where('is_publish', true)->get();
        if ($products->isEmpty() || !$products) {
            return response()->json([
                "message" => "No published products found",
                "success" => false
            ], 500);
        }
        return response()->json([
            "data" => $products,
            "success" => true
        ], 200);
    }

    // public function addView($productId)
    // {
    //     $userId = Auth::id();
    //     $ipAddress = FacadesRequest::ip();
    //     $recentView = ProductView::where('product_id', $productId);
    //     if ($userId) {
    //         // eger login olubsa user_id sini table-a elave edecik
    //         $recentView->where('user_id', $userId);
    //     } else {
    //         // eger login olmayibsa ip address-ini table-a elave edecik
    //         $recentView->where('id_address', $ipAddress);
    //     }
    //     //yoxlayiriq umumiyyetle bele bir user productId-li mehsula baxib mi(eger baxibsa baxis deyerini artirmaga ehtiyac yoxdu)
    //     $recentView = $recentView->first();

    //     // eger bu mehsula bu user hele baxmayibsa
    //     if (!$recentView) {
    //         ProductView::create([
    //             'product_id' => $productId,
    //             'user_id' => $userId, //login olmayan user-ler ucun NULL elave olunacaq table-a
    //             'id_address' => $userId ? null : $ipAddress,
    //             'viewed_at' => Carbon::now(),
    //         ]);
    //     }

    //     return response()->json(['message' => 'View added successfully', "user_id" => $userId, "ip_address" => $ipAddress], 200);
    // }

    public function addView(Request $request, $productId)
    {
        $userId = Auth::id();
        $ipAddress = $request->ip(); // Используем $request->ip() для получения IP-адреса

        $recentView = ProductView::where('product_id', $productId);

        if ($userId) {
            // Если пользователь авторизован, проверяем по user_id
            $recentView->where('user_id', $userId);
        } else {
            // Если пользователь не авторизован, проверяем по id_address
            $recentView->where('id_address', $ipAddress);
        }

        // Проверяем, есть ли уже просмотр для данного пользователя или IP-адреса
        $recentView = $recentView->first();

        // Если ещё нет просмотра для данного пользователя или IP-адреса
        if (!$recentView) {
            ProductView::create([
                'product_id' => $productId,
                'user_id' => $userId, // Для неавторизованных пользователей будет NULL
                'id_address' => $userId ? null : $ipAddress,
                'viewed_at' => Carbon::now(),
            ]);
        }

        return response()->json(['message' => 'View added successfully', "user_id" => $userId, "id_address" => $ipAddress], 200);
    }

    public function reviews($productId)
    {
        $reviews = ProductView::where('product_id', $productId)->count();
        if ($reviews) {
            return response()->json([
                "data" => $reviews,
                "success" => true
            ], 200);
        }
        return response()->json([
            "message" => 'No reviews found',
            "success" => false
        ]);
    }
}
