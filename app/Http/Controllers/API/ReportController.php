<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Get sales report.
     */
    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'in:day,week,month',
        ]);

        $data = $this->reportService->getSalesReport(
            $request->start_date,
            $request->end_date,
            $request->get('group_by', 'day')
        );

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Get popular items report.
     */
    public function popularItems(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'integer|min:1|max:50',
        ]);

        $data = $this->reportService->getPopularItemsReport(
            $request->start_date,
            $request->end_date,
            $request->get('limit', 10)
        );

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Get revenue report.
     */
    public function revenue(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $this->reportService->getRevenueReport(
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Export reports.
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:sales,popular_items,revenue',
            'format' => 'required|in:pdf,excel',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $filePath = $this->reportService->exportReport(
                $request->type,
                $request->format,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'message' => 'Report exported successfully',
                'download_url' => asset('storage/' . $filePath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export report',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
