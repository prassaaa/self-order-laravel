<?php

namespace App\Http\Controllers\API;

use App\Events\PaymentProcessed;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Payment::with(['order', 'processedBy']);

        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by processed_by
        if ($request->filled('processed_by')) {
            $query->where('processed_by', $request->processed_by);
        }

        // Order by latest
        $query->latest();

        // Pagination
        $perPage = $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        return PaymentResource::collection($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->createPayment($request->validated());

            return response()->json([
                'message' => 'Payment created successfully',
                'data' => new PaymentResource($payment->load(['order', 'processedBy']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'data' => new PaymentResource($payment->load(['order', 'processedBy']))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, Payment $payment): JsonResponse
    {
        try {
            $updatedPayment = $this->paymentService->updatePayment($payment, $request->validated());

            return response()->json([
                'message' => 'Payment updated successfully',
                'data' => new PaymentResource($updatedPayment->load(['order', 'processedBy']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        if ($payment->status === 'completed') {
            return response()->json([
                'message' => 'Cannot delete completed payment'
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Get payments for a specific order.
     */
    public function orderPayments(Order $order): AnonymousResourceCollection
    {
        $payments = $order->payments()->with('processedBy')->latest()->get();

        return PaymentResource::collection($payments);
    }

    /**
     * Process payment for a specific order.
     */
    public function processPayment(PaymentRequest $request, Order $order): JsonResponse
    {
        try {
            $payment = $this->paymentService->processOrderPayment($order, $request->validated());

            // Broadcast payment processed event
            broadcast(new PaymentProcessed($payment));

            return response()->json([
                'message' => 'Payment processed successfully',
                'data' => new PaymentResource($payment->load(['order', 'processedBy']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
