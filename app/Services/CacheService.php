<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * Cache duration constants (in seconds).
     */
    const CACHE_DURATION_SHORT = 300;    // 5 minutes
    const CACHE_DURATION_MEDIUM = 1800;  // 30 minutes
    const CACHE_DURATION_LONG = 3600;    // 1 hour
    const CACHE_DURATION_DAILY = 86400;  // 24 hours

    /**
     * Cache key prefixes.
     */
    const PREFIX_MENU = 'menu:';
    const PREFIX_CATEGORY = 'category:';
    const PREFIX_ORDER = 'order:';
    const PREFIX_STATS = 'stats:';
    const PREFIX_REPORT = 'report:';

    /**
     * Get cached menu data.
     */
    public function getMenus(array $filters = []): mixed
    {
        $cacheKey = $this->generateCacheKey(self::PREFIX_MENU, 'list', $filters);
        
        return Cache::remember($cacheKey, self::CACHE_DURATION_MEDIUM, function () use ($filters) {
            $query = \App\Models\Menu::with(['category'])
                ->where('is_available', true);

            if (isset($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (isset($filters['search'])) {
                $query->where('name', 'like', '%' . $filters['search'] . '%');
            }

            return $query->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get cached categories.
     */
    public function getCategories(): mixed
    {
        $cacheKey = self::PREFIX_CATEGORY . 'active_list';
        
        return Cache::remember($cacheKey, self::CACHE_DURATION_LONG, function () {
            return \App\Models\Category::where('is_active', true)
                ->withCount('menus')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get cached dashboard statistics.
     */
    public function getDashboardStats(): mixed
    {
        $cacheKey = self::PREFIX_STATS . 'dashboard:' . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION_SHORT, function () {
            $today = now()->startOfDay();
            
            return [
                'total_orders' => \App\Models\Order::count(),
                'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
                'completed_orders' => \App\Models\Order::where('status', 'completed')->count(),
                'today_orders' => \App\Models\Order::whereDate('created_at', $today)->count(),
                'today_revenue' => \App\Models\Order::whereDate('created_at', $today)
                    ->where('status', 'completed')
                    ->sum('total_amount'),
                'total_revenue' => \App\Models\Order::where('status', 'completed')
                    ->sum('total_amount'),
                'popular_items' => $this->getPopularItems(),
                'recent_orders' => $this->getRecentOrders(),
            ];
        });
    }

    /**
     * Get cached popular items.
     */
    public function getPopularItems(int $limit = 5): mixed
    {
        $cacheKey = self::PREFIX_STATS . 'popular_items:' . now()->format('Y-m-d');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION_MEDIUM, function () use ($limit) {
            return \App\Models\OrderItem::select('menu_id')
                ->selectRaw('SUM(quantity) as total_ordered')
                ->selectRaw('SUM(subtotal) as revenue')
                ->with(['menu:id,name,category_id', 'menu.category:id,name'])
                ->whereHas('order', function ($query) {
                    $query->where('status', 'completed');
                })
                ->groupBy('menu_id')
                ->orderByDesc('total_ordered')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get cached recent orders.
     */
    public function getRecentOrders(int $limit = 10): mixed
    {
        $cacheKey = self::PREFIX_ORDER . 'recent:' . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION_SHORT, function () use ($limit) {
            return \App\Models\Order::with(['orderItems.menu'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get cached sales report.
     */
    public function getSalesReport(string $startDate, string $endDate): mixed
    {
        $cacheKey = $this->generateCacheKey(self::PREFIX_REPORT, 'sales', [
            'start' => $startDate,
            'end' => $endDate
        ]);
        
        return Cache::remember($cacheKey, self::CACHE_DURATION_MEDIUM, function () use ($startDate, $endDate) {
            return [
                'total_orders' => \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])->count(),
                'completed_orders' => \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')->count(),
                'total_revenue' => \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')->sum('total_amount'),
                'payment_methods' => $this->getPaymentMethodStats($startDate, $endDate),
                'hourly_sales' => $this->getHourlySales($startDate, $endDate),
            ];
        });
    }

    /**
     * Clear specific cache.
     */
    public function clearCache(string $pattern): void
    {
        if (config('cache.default') === 'redis') {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } else {
            // For other cache drivers, we'll need to clear all cache
            Cache::flush();
        }
    }

    /**
     * Clear menu-related cache.
     */
    public function clearMenuCache(): void
    {
        $this->clearCache(self::PREFIX_MENU . '*');
        $this->clearCache(self::PREFIX_CATEGORY . '*');
    }

    /**
     * Clear order-related cache.
     */
    public function clearOrderCache(): void
    {
        $this->clearCache(self::PREFIX_ORDER . '*');
        $this->clearCache(self::PREFIX_STATS . '*');
    }

    /**
     * Clear all application cache.
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Generate cache key.
     */
    protected function generateCacheKey(string $prefix, string $type, array $params = []): string
    {
        $key = $prefix . $type;
        
        if (!empty($params)) {
            ksort($params);
            $key .= ':' . md5(serialize($params));
        }
        
        return $key;
    }

    /**
     * Get payment method statistics.
     */
    protected function getPaymentMethodStats(string $startDate, string $endDate): array
    {
        return \App\Models\Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('method')
            ->get()
            ->toArray();
    }

    /**
     * Get hourly sales data.
     */
    protected function getHourlySales(string $startDate, string $endDate): array
    {
        return \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    /**
     * Warm up cache with frequently accessed data.
     */
    public function warmUpCache(): void
    {
        // Warm up categories
        $this->getCategories();
        
        // Warm up menus
        $this->getMenus();
        
        // Warm up dashboard stats
        $this->getDashboardStats();
        
        // Warm up popular items
        $this->getPopularItems();
        
        // Warm up recent orders
        $this->getRecentOrders();
    }
}
