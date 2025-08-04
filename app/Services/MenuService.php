<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Get available menus with caching.
     */
    public function getAvailableMenus(array $filters = []): Collection
    {
        $cacheKey = 'available_menus_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            $query = Menu::with('category')->available()->ordered();

            // Apply filters
            if (isset($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (isset($filters['search'])) {
                $query->search($filters['search']);
            }

            if (isset($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }

            if (isset($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }

            return $query->get();
        });
    }

    /**
     * Get menu by ID with availability check.
     */
    public function getMenuById(int $menuId): Menu
    {
        $menu = Menu::with('category')->findOrFail($menuId);

        if (!$menu->is_available) {
            throw new \Exception("Menu '{$menu->name}' is not available");
        }

        return $menu;
    }

    /**
     * Check menu availability.
     */
    public function checkAvailability(int $menuId): bool
    {
        return Menu::where('id', $menuId)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Bulk check menu availability.
     */
    public function checkBulkAvailability(array $menuIds): array
    {
        $availableMenus = Menu::whereIn('id', $menuIds)
            ->where('is_available', true)
            ->pluck('id')
            ->toArray();

        $result = [];
        foreach ($menuIds as $menuId) {
            $result[$menuId] = in_array($menuId, $availableMenus);
        }

        return $result;
    }

    /**
     * Get popular menus based on order frequency.
     */
    public function getPopularMenus(int $limit = 10, string $period = '30 days'): Collection
    {
        $cacheKey = "popular_menus_{$limit}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($limit, $period) {
            return Menu::with('category')
                ->select('menus.*')
                ->selectRaw('SUM(order_items.quantity) as total_ordered')
                ->join('order_items', 'menus.id', '=', 'order_items.menu_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.created_at', '>=', now()->sub($period))
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('menus.id')
                ->orderByDesc('total_ordered')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get menu recommendations based on category.
     */
    public function getRecommendations(int $categoryId, int $limit = 5): Collection
    {
        return Menu::with('category')
            ->where('category_id', $categoryId)
            ->where('is_available', true)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Update menu availability.
     */
    public function updateAvailability(int $menuId, bool $isAvailable): Menu
    {
        $menu = Menu::findOrFail($menuId);
        $menu->update(['is_available' => $isAvailable]);

        // Clear cache
        $this->clearMenuCache();

        return $menu;
    }

    /**
     * Bulk update menu availability.
     */
    public function bulkUpdateAvailability(array $menuIds, bool $isAvailable): int
    {
        $updated = Menu::whereIn('id', $menuIds)
            ->update(['is_available' => $isAvailable]);

        // Clear cache
        $this->clearMenuCache();

        return $updated;
    }

    /**
     * Get menu statistics.
     */
    public function getMenuStatistics(): array
    {
        return [
            'total_menus' => Menu::count(),
            'available_menus' => Menu::where('is_available', true)->count(),
            'unavailable_menus' => Menu::where('is_available', false)->count(),
            'menus_by_category' => Category::withCount('menus')->get()->pluck('menus_count', 'name'),
            'average_price' => Menu::where('is_available', true)->avg('price'),
            'price_range' => [
                'min' => Menu::where('is_available', true)->min('price'),
                'max' => Menu::where('is_available', true)->max('price'),
            ],
        ];
    }

    /**
     * Search menus with advanced filters.
     */
    public function searchMenus(array $criteria): Collection
    {
        $query = Menu::with('category')->available();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', '%' . $criteria['name'] . '%');
        }

        if (isset($criteria['description'])) {
            $query->where('description', 'like', '%' . $criteria['description'] . '%');
        }

        if (isset($criteria['category_ids'])) {
            $query->whereIn('category_id', $criteria['category_ids']);
        }

        if (isset($criteria['price_min'])) {
            $query->where('price', '>=', $criteria['price_min']);
        }

        if (isset($criteria['price_max'])) {
            $query->where('price', '<=', $criteria['price_max']);
        }

        if (isset($criteria['sort_by'])) {
            switch ($criteria['sort_by']) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'popularity':
                    // This would require a more complex query with order_items
                    $query->ordered();
                    break;
                default:
                    $query->ordered();
            }
        } else {
            $query->ordered();
        }

        return $query->get();
    }

    /**
     * Clear menu-related cache.
     */
    public function clearMenuCache(): void
    {
        Cache::forget('available_menus_*');
        Cache::forget('popular_menus_*');
    }

    /**
     * Get menu pricing tiers.
     */
    public function getPricingTiers(): array
    {
        $prices = Menu::where('is_available', true)->pluck('price')->sort();
        
        if ($prices->isEmpty()) {
            return [];
        }

        $min = $prices->min();
        $max = $prices->max();
        $range = $max - $min;

        return [
            'budget' => ['min' => $min, 'max' => $min + ($range * 0.33)],
            'mid_range' => ['min' => $min + ($range * 0.33), 'max' => $min + ($range * 0.66)],
            'premium' => ['min' => $min + ($range * 0.66), 'max' => $max],
        ];
    }
}
