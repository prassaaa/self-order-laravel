<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    /**
     * Get all categories.
     */
    public function getAll(array $filters = []): Collection
    {
        $cacheKey = 'categories_all_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 600, function () use ($filters) {
            $query = Category::query();

            $this->applyFilters($query, $filters);

            return $query->ordered()->get();
        });
    }

    /**
     * Get active categories only.
     */
    public function getActive(): Collection
    {
        return $this->getAll(['active' => true]);
    }

    /**
     * Get categories with menu count.
     */
    public function getWithMenuCount(bool $activeOnly = true): Collection
    {
        $cacheKey = "categories_with_menu_count_{$activeOnly}";
        
        return Cache::remember($cacheKey, 600, function () use ($activeOnly) {
            $query = Category::withCount(['menus' => function ($q) use ($activeOnly) {
                if ($activeOnly) {
                    $q->where('is_available', true);
                }
            }]);

            if ($activeOnly) {
                $query->active();
            }

            return $query->ordered()->get();
        });
    }

    /**
     * Find category by ID.
     */
    public function findById(int $id): ?Category
    {
        return Cache::remember("category_{$id}", 600, function () use ($id) {
            return Category::find($id);
        });
    }

    /**
     * Find category by ID or fail.
     */
    public function findByIdOrFail(int $id): Category
    {
        $category = $this->findById($id);
        
        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Category with ID {$id} not found");
        }

        return $category;
    }

    /**
     * Find category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        return Cache::remember("category_slug_{$slug}", 600, function () use ($slug) {
            return Category::where('slug', $slug)->first();
        });
    }

    /**
     * Find category by slug or fail.
     */
    public function findBySlugOrFail(string $slug): Category
    {
        $category = $this->findBySlug($slug);
        
        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Category with slug '{$slug}' not found");
        }

        return $category;
    }

    /**
     * Get category with its menus.
     */
    public function getWithMenus(int $id, bool $availableOnly = true): ?Category
    {
        $cacheKey = "category_with_menus_{$id}_{$availableOnly}";
        
        return Cache::remember($cacheKey, 300, function () use ($id, $availableOnly) {
            $query = Category::with(['menus' => function ($q) use ($availableOnly) {
                if ($availableOnly) {
                    $q->where('is_available', true);
                }
                $q->ordered();
            }]);

            return $query->find($id);
        });
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        $category = Category::create($data);
        $this->clearCache();
        return $category;
    }

    /**
     * Update a category.
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        $this->clearCache();
        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        $result = $category->delete();
        $this->clearCache();
        return $result;
    }

    /**
     * Update category status.
     */
    public function updateStatus(int $categoryId, bool $isActive): bool
    {
        $result = Category::where('id', $categoryId)->update(['is_active' => $isActive]);
        $this->clearCache();
        return $result > 0;
    }

    /**
     * Update sort order.
     */
    public function updateSortOrder(int $categoryId, int $sortOrder): bool
    {
        $result = Category::where('id', $categoryId)->update(['sort_order' => $sortOrder]);
        $this->clearCache();
        return $result > 0;
    }

    /**
     * Bulk update sort orders.
     */
    public function bulkUpdateSortOrder(array $categoryOrders): bool
    {
        $success = true;
        
        foreach ($categoryOrders as $categoryId => $sortOrder) {
            $result = Category::where('id', $categoryId)->update(['sort_order' => $sortOrder]);
            if (!$result) {
                $success = false;
            }
        }

        $this->clearCache();
        return $success;
    }

    /**
     * Get category statistics.
     */
    public function getStatistics(): array
    {
        return Cache::remember('category_statistics', 3600, function () {
            return [
                'total' => Category::count(),
                'active' => Category::where('is_active', true)->count(),
                'inactive' => Category::where('is_active', false)->count(),
                'with_menus' => Category::has('menus')->count(),
                'without_menus' => Category::doesntHave('menus')->count(),
            ];
        });
    }

    /**
     * Search categories by name.
     */
    public function search(string $query): Collection
    {
        return Category::where('name', 'like', '%' . $query . '%')
            ->ordered()
            ->get();
    }

    /**
     * Get categories for dropdown/select options.
     */
    public function getForSelect(bool $activeOnly = true): array
    {
        $cacheKey = "categories_for_select_{$activeOnly}";
        
        return Cache::remember($cacheKey, 3600, function () use ($activeOnly) {
            $query = Category::select('id', 'name');

            if ($activeOnly) {
                $query->active();
            }

            return $query->ordered()->pluck('name', 'id')->toArray();
        });
    }

    /**
     * Check if category has menus.
     */
    public function hasMenus(int $categoryId): bool
    {
        return Category::where('id', $categoryId)->has('menus')->exists();
    }

    /**
     * Get next sort order.
     */
    public function getNextSortOrder(): int
    {
        return Category::max('sort_order') + 1;
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['has_menus'])) {
            if ($filters['has_menus']) {
                $query->has('menus');
            } else {
                $query->doesntHave('menus');
            }
        }
    }

    /**
     * Clear category-related cache.
     */
    public function clearCache(): void
    {
        Cache::flush(); // For simplicity, we'll flush all cache
    }
}
