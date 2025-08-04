<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Category;
use App\Models\Menu;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Menu::with('category');

        // Filter by availability
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Order by sort_order and name
        $query->ordered();

        // Pagination
        $perPage = $request->get('per_page', 15);
        $menus = $query->paginate($perPage);

        return MenuResource::collection($menus);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->uploadImage(
                $request->file('image'),
                'menus'
            );
        }

        $menu = Menu::create($data);
        $menu->load('category');

        return response()->json([
            'message' => 'Menu created successfully',
            'data' => new MenuResource($menu)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu): JsonResponse
    {
        return response()->json([
            'data' => new MenuResource($menu->load('category'))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MenuRequest $request, Menu $menu): JsonResponse
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($menu->image) {
                $this->imageService->deleteImage($menu->image);
            }

            $data['image'] = $this->imageService->uploadImage(
                $request->file('image'),
                'menus'
            );
        }

        $menu->update($data);
        $menu->load('category');

        return response()->json([
            'message' => 'Menu updated successfully',
            'data' => new MenuResource($menu)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        // Check if menu has order items
        if ($menu->orderItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete menu with existing orders'
            ], 422);
        }

        // Delete image
        if ($menu->image) {
            $this->imageService->deleteImage($menu->image);
        }

        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully'
        ]);
    }

    /**
     * Get menus by category.
     */
    public function byCategory(Category $category, Request $request): AnonymousResourceCollection
    {
        $query = $category->menus()->with('category');

        // Filter by availability
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Search by name or description
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Order by sort_order and name
        $query->ordered();

        $menus = $query->get();

        return MenuResource::collection($menus);
    }
}
