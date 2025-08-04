<?php

namespace App\Http\Controllers\API;

/**
 * @OA\Schema(
 *     schema="Menu",
 *     type="object",
 *     title="Menu",
 *     description="Menu item model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Nasi Goreng Spesial"),
 *     @OA\Property(property="description", type="string", example="Nasi goreng dengan telur, ayam, dan sayuran"),
 *     @OA\Property(property="price", type="number", format="float", example=25000),
 *     @OA\Property(property="formatted_price", type="string", example="Rp 25.000"),
 *     @OA\Property(property="image", type="string", example="menus/nasi-goreng.jpg"),
 *     @OA\Property(property="image_url", type="string", example="http://localhost/storage/menus/nasi-goreng.jpg"),
 *     @OA\Property(property="is_available", type="boolean", example=true),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="category", ref="#/components/schemas/Category")
 * )
 * 
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     description="Menu category model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Main Course"),
 *     @OA\Property(property="slug", type="string", example="main-course"),
 *     @OA\Property(property="description", type="string", example="Main course dishes"),
 *     @OA\Property(property="image", type="string", example="categories/main-course.jpg"),
 *     @OA\Property(property="image_url", type="string", example="http://localhost/storage/categories/main-course.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="menus_count", type="integer", example=5),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     description="Order model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD-001"),
 *     @OA\Property(property="table_number", type="string", example="T-01"),
 *     @OA\Property(property="customer_name", type="string", example="John Doe"),
 *     @OA\Property(property="customer_phone", type="string", example="081234567890"),
 *     @OA\Property(property="status", type="string", enum={"pending", "confirmed", "preparing", "ready", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="payment_status", type="string", enum={"pending", "paid", "failed", "refunded"}, example="pending"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=75000),
 *     @OA\Property(property="formatted_total", type="string", example="Rp 75.000"),
 *     @OA\Property(property="notes", type="string", example="Extra spicy"),
 *     @OA\Property(property="is_paid", type="boolean", example=false),
 *     @OA\Property(property="can_be_cancelled", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at_human", type="string", example="2 minutes ago"),
 *     @OA\Property(property="order_items", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 *     @OA\Property(property="payments", type="array", @OA\Items(ref="#/components/schemas/Payment"))
 * )
 * 
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     title="OrderItem",
 *     description="Order item model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="menu_id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="price", type="number", format="float", example=25000),
 *     @OA\Property(property="subtotal", type="number", format="float", example=50000),
 *     @OA\Property(property="notes", type="string", example="No onions"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="menu", ref="#/components/schemas/Menu")
 * )
 * 
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     title="Payment",
 *     description="Payment model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="amount", type="number", format="float", example=75000),
 *     @OA\Property(property="method", type="string", enum={"cash", "qris", "bank_transfer", "e_wallet"}, example="cash"),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed", "failed", "refunded"}, example="completed"),
 *     @OA\Property(property="transaction_id", type="string", example="TXN-123456"),
 *     @OA\Property(property="notes", type="string", example="Cash payment"),
 *     @OA\Property(property="processed_by", type="integer", example=1),
 *     @OA\Property(property="processed_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time"),
 *     @OA\Property(property="avatar", type="string", example="avatars/john.jpg"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"admin"}),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="DashboardStats",
 *     type="object",
 *     title="DashboardStats",
 *     description="Dashboard statistics",
 *     @OA\Property(property="total_orders", type="integer", example=150),
 *     @OA\Property(property="pending_orders", type="integer", example=5),
 *     @OA\Property(property="completed_orders", type="integer", example=140),
 *     @OA\Property(property="today_orders", type="integer", example=25),
 *     @OA\Property(property="today_revenue", type="number", format="float", example=1500000),
 *     @OA\Property(property="total_revenue", type="number", format="float", example=15000000),
 *     @OA\Property(property="popular_items", type="array", @OA\Items(ref="#/components/schemas/PopularItem")),
 *     @OA\Property(property="recent_orders", type="array", @OA\Items(ref="#/components/schemas/Order"))
 * )
 * 
 * @OA\Schema(
 *     schema="PopularItem",
 *     type="object",
 *     title="PopularItem",
 *     description="Popular menu item statistics",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Nasi Goreng Spesial"),
 *     @OA\Property(property="total_ordered", type="integer", example=50),
 *     @OA\Property(property="revenue", type="number", format="float", example=1250000)
 * )
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     title="ApiResponse",
 *     description="Standard API response",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(property="data", type="object")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="ValidationError",
 *     description="Validation error response",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(property="errors", type="object", 
 *         @OA\Property(property="field_name", type="array", @OA\Items(type="string", example="The field is required."))
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="ErrorResponse",
 *     description="Error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
 *     @OA\Property(property="error", type="string", example="Detailed error message")
 * )
 */
class Schemas
{
    // This class is only used for Swagger schema definitions
}
