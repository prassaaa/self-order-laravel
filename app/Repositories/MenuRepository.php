<?php

namespace App\Repositories;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class MenuRepository
{
    /**
     * Get all menus with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $cacheKey = 'menus_all_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            $query = Menu::with('category');

            $this->applyFilters($query, $filters);

            return $query->ordered()->get();
        });
    }

    /**
     * Get paginated menus with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Menu::with('category');

        $this->applyFilters($query, $filters);

        return $query->ordered()->paginate($perPage);
    }

    /**
     * Get available menus only.
     */
    public function getAvailable(array $filters = []): Collection
    {
        $filters['available'] = true;
        return $this->getAll($filters);
    }

    /**
     * Find menu by ID.
     */
    public function findById(int $id): ?Menu
    {
        return Cache::remember("menu_{$id}", 300, function () use ($id) {
            return Menu::with('category')->find($id);
        });
    }

    /**
     * Find menu by ID or fail.
     */
    public function findByIdOrFail(int $id): Menu
    {
        $menu = $this->findById($id);

        if (!$menu) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Menu with ID {$id} not found");
        }

        return $menu;
    }

    /**
     * Get menus by category.
     */
    public function getByCategory(int $categoryId, array $filters = []): Collection
    {
        $filters['category_id'] = $categoryId;
        return $this->getAll($filters);
    }

    /**
     * Search menus by name or description.
     */
    public function search(string $query, array $filters = []): Collection
    {
        $filters['search'] = $query;
        return $this->getAll($filters);
    }

    /**
     * Get popular menus based on order frequency.
     */
    public function getPopular(int $limit = 10, int $days = 30): Collection
    {
        $cacheKey = "popular_menus_{$limit}_{$days}";

        return Cache::remember($cacheKey, 3600, function () use ($limit, $days) {
            return Menu::with('category')
                ->select('menus.*')
                ->selectRaw('SUM(order_items.quantity) as total_ordered')
                ->join('order_items', 'menus.id', '=', 'order_items.menu_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.created_at', '>=', now()->subDays($days))
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('menus.id')
                ->orderByDesc('total_ordered')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get menus by price range.
     */
    public function getByPriceRange(float $minPrice, float $maxPrice, array $filters = []): Collection
    {
        $filters['min_price'] = $minPrice;
        $filters['max_price'] = $maxPrice;
        return $this->getAll($filters);
    }

    /**
     * Create a new menu.
     */
    public function create(array $data): Menu
    {
        $menu = Menu::create($data);
        $this->clearCache();
        return $menu->load('category');
    }

    /**
     * Update a menu.
     */
    public function update(Menu $menu, array $data): Menu
    {
        $menu->update($data);
        $this->clearCache();
        return $menu->fresh(['category']);
    }

    /**
     * Delete a menu.
     */
    public function delete(Menu $menu): bool
    {
        $result = $menu->delete();
        $this->clearCache();
        return $result;
    }

    /**
     * Update menu availability.
     */
    public function updateAvailability(int $menuId, bool $isAvailable): bool
    {
        $result = Menu::where('id', $menuId)->update(['is_available' => $isAvailable]);
        $this->clearCache();
        return $result > 0;
    }

    /**
     * Bulk update menu availability.
     */
    public function bulkUpdateAvailability(array $menuIds, bool $isAvailable): int
    {
        $result = Menu::whereIn('id', $menuIds)->update(['is_available' => $isAvailable]);
        $this->clearCache();
        return $result;
    }

    /**
     * Get menu statistics.
     */
    public function getStatistics(): array
    {
        return Cache::remember('menu_statistics', 3600, function () {
            return [
                'total' => Menu::count(),
                'available' => Menu::where('is_available', true)->count(),
                'unavailable' => Menu::where('is_available', false)->count(),
                'average_price' => Menu::where('is_available', true)->avg('price'),
                'min_price' => Menu::where('is_available', true)->min('price'),
                'max_price' => Menu::where('is_available', true)->max('price'),
            ];
        });
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['available'])) {
            $query->where('is_available', $filters['available']);
        }

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

        if (isset($filters['category_ids']) && is_array($filters['category_ids'])) {
            $query->whereIn('category_id', $filters['category_ids']);
        }
    }

    /**
     * Clear menu-related cache.
     */
    public function clearCache(): void
    {
        Cache::flush(); // For simplicity, we'll flush all cache
        // In production, you might want to be more selective:
        // Cache::tags(['menus'])->flush();
    }
}
