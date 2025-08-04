<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:monitor {--report : Generate performance report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor application performance and generate reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting performance monitoring...');

        $metrics = $this->collectMetrics();
        
        if ($this->option('report')) {
            $this->generateReport($metrics);
        } else {
            $this->displayMetrics($metrics);
        }

        $this->logMetrics($metrics);
        
        return Command::SUCCESS;
    }

    /**
     * Collect performance metrics.
     */
    protected function collectMetrics(): array
    {
        $startTime = microtime(true);

        $metrics = [
            'timestamp' => now()->toISOString(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'memory' => $this->getMemoryMetrics(),
            'queries' => $this->getQueryMetrics(),
            'response_times' => $this->getResponseTimeMetrics(),
        ];

        $metrics['collection_time'] = round((microtime(true) - $startTime) * 1000, 2);

        return $metrics;
    }

    /**
     * Get database performance metrics.
     */
    protected function getDatabaseMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test database connection
        try {
            DB::connection()->getPdo();
            $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            $connected = true;
        } catch (\Exception $e) {
            $connectionTime = null;
            $connected = false;
        }

        // Get table sizes
        $tableSizes = $this->getTableSizes();

        // Get slow queries (if query log is enabled)
        $slowQueries = $this->getSlowQueries();

        return [
            'connected' => $connected,
            'connection_time_ms' => $connectionTime,
            'table_sizes' => $tableSizes,
            'slow_queries_count' => count($slowQueries),
            'total_connections' => $this->getDatabaseConnections(),
        ];
    }

    /**
     * Get cache performance metrics.
     */
    protected function getCacheMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test cache connection
        try {
            Cache::put('performance_test', 'test', 1);
            $testValue = Cache::get('performance_test');
            Cache::forget('performance_test');
            
            $cacheTime = round((microtime(true) - $startTime) * 1000, 2);
            $working = $testValue === 'test';
        } catch (\Exception $e) {
            $cacheTime = null;
            $working = false;
        }

        return [
            'working' => $working,
            'response_time_ms' => $cacheTime,
            'driver' => config('cache.default'),
            'hit_rate' => $this->getCacheHitRate(),
        ];
    }

    /**
     * Get memory usage metrics.
     */
    protected function getMemoryMetrics(): array
    {
        return [
            'current_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'limit_mb' => $this->getMemoryLimit(),
            'usage_percentage' => $this->getMemoryUsagePercentage(),
        ];
    }

    /**
     * Get query performance metrics.
     */
    protected function getQueryMetrics(): array
    {
        // Enable query logging temporarily
        DB::enableQueryLog();
        
        // Run some test queries
        $startTime = microtime(true);
        
        DB::table('orders')->count();
        DB::table('menus')->where('is_available', true)->count();
        DB::table('categories')->where('is_active', true)->count();
        
        $queryTime = round((microtime(true) - $startTime) * 1000, 2);
        $queries = DB::getQueryLog();
        
        DB::disableQueryLog();

        return [
            'test_queries_time_ms' => $queryTime,
            'test_queries_count' => count($queries),
            'average_query_time_ms' => count($queries) > 0 ? round($queryTime / count($queries), 2) : 0,
        ];
    }

    /**
     * Get response time metrics.
     */
    protected function getResponseTimeMetrics(): array
    {
        // This would typically come from application logs or APM tools
        return [
            'average_response_time_ms' => 150, // Mock data
            'p95_response_time_ms' => 300,     // Mock data
            'p99_response_time_ms' => 500,     // Mock data
        ];
    }

    /**
     * Display metrics in console.
     */
    protected function displayMetrics(array $metrics): void
    {
        $this->info('Performance Metrics:');
        $this->line('');

        // Database metrics
        $this->info('Database:');
        $db = $metrics['database'];
        $this->line("  Connected: " . ($db['connected'] ? 'Yes' : 'No'));
        if ($db['connection_time_ms']) {
            $this->line("  Connection Time: {$db['connection_time_ms']}ms");
        }
        $this->line("  Total Connections: {$db['total_connections']}");
        $this->line('');

        // Cache metrics
        $this->info('Cache:');
        $cache = $metrics['cache'];
        $this->line("  Working: " . ($cache['working'] ? 'Yes' : 'No'));
        $this->line("  Driver: {$cache['driver']}");
        if ($cache['response_time_ms']) {
            $this->line("  Response Time: {$cache['response_time_ms']}ms");
        }
        $this->line('');

        // Memory metrics
        $this->info('Memory:');
        $memory = $metrics['memory'];
        $this->line("  Current Usage: {$memory['current_usage_mb']}MB");
        $this->line("  Peak Usage: {$memory['peak_usage_mb']}MB");
        $this->line("  Usage Percentage: {$memory['usage_percentage']}%");
        $this->line('');

        // Query metrics
        $this->info('Queries:');
        $queries = $metrics['queries'];
        $this->line("  Test Queries Time: {$queries['test_queries_time_ms']}ms");
        $this->line("  Average Query Time: {$queries['average_query_time_ms']}ms");
    }

    /**
     * Generate performance report.
     */
    protected function generateReport(array $metrics): void
    {
        $reportPath = storage_path('logs/performance-report-' . now()->format('Y-m-d-H-i-s') . '.json');
        file_put_contents($reportPath, json_encode($metrics, JSON_PRETTY_PRINT));
        
        $this->info("Performance report generated: {$reportPath}");
    }

    /**
     * Log metrics for monitoring.
     */
    protected function logMetrics(array $metrics): void
    {
        Log::channel('performance')->info('Performance metrics collected', $metrics);
    }

    /**
     * Get table sizes.
     */
    protected function getTableSizes(): array
    {
        try {
            $tables = ['orders', 'order_items', 'menus', 'categories', 'payments', 'users'];
            $sizes = [];
            
            foreach ($tables as $table) {
                $sizes[$table] = DB::table($table)->count();
            }
            
            return $sizes;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get slow queries.
     */
    protected function getSlowQueries(): array
    {
        // This would typically query slow query log
        return [];
    }

    /**
     * Get database connections count.
     */
    protected function getDatabaseConnections(): int
    {
        try {
            $result = DB::select('SHOW STATUS LIKE "Threads_connected"');
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get cache hit rate.
     */
    protected function getCacheHitRate(): float
    {
        // This would typically come from cache statistics
        return 85.5; // Mock data
    }

    /**
     * Get memory limit.
     */
    protected function getMemoryLimit(): float
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return -1;
        }
        
        return round($this->convertToBytes($limit) / 1024 / 1024, 2);
    }

    /**
     * Get memory usage percentage.
     */
    protected function getMemoryUsagePercentage(): float
    {
        $limit = $this->getMemoryLimit();
        if ($limit === -1) {
            return 0;
        }
        
        $current = memory_get_usage(true) / 1024 / 1024;
        return round(($current / $limit) * 100, 2);
    }

    /**
     * Convert memory string to bytes.
     */
    protected function convertToBytes(string $value): int
    {
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}
