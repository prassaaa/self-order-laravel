<?php

namespace App\Http\Controllers\API;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::with(['orderItems.menu', 'payments']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by table number
        if ($request->filled('table_number')) {
            $query->where('table_number', $request->table_number);
        }

        // Search by order number or customer name
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%');
            });
        }

        // Order by latest
        $query->latest();

        // Pagination
        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());

            // Broadcast order created event
            broadcast(new OrderCreated($order));

            return response()->json([
                'message' => 'Order created successfully',
                'data' => new OrderResource($order->load(['orderItems.menu', 'payments']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'data' => new OrderResource($order->load(['orderItems.menu', 'payments']))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->updateOrder($order, $request->validated());

            return response()->json([
                'message' => 'Order updated successfully',
                'data' => new OrderResource($updatedOrder->load(['orderItems.menu', 'payments']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(OrderStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->updateOrderStatus($order, $request->status);

            // Broadcast order status updated event
            broadcast(new OrderStatusUpdated($updatedOrder));

            return response()->json([
                'message' => 'Order status updated successfully',
                'data' => new OrderResource($updatedOrder->load(['orderItems.menu', 'payments']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order): JsonResponse
    {
        if (!$order->canBeCancelled()) {
            return response()->json([
                'message' => 'Order cannot be cancelled'
            ], 422);
        }

        try {
            $this->orderService->cancelOrder($order);

            return response()->json([
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
