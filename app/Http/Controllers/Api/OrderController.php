<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Create a new order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,paid,canceled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.product_name' => 'nullable|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check stock before creating order
        $insufficientStock = [];
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'errors' => ['items' => ['Product ID ' . $item['product_id'] . ' does not exist.']]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $currentStock = (int) ($product->stock ?? 0);
            $requestedQty = (int) $item['qty'];
            if ($currentStock < $requestedQty) {
                $insufficientStock[] = $product->name . ': requested ' . $requestedQty . ', only ' . $currentStock . ' in stock';
            }
        }
        if (!empty($insufficientStock)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock for one or more products',
                'errors' => ['stock' => $insufficientStock]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::create([
                'user_id' => $request->user_id,
                'total_amount' => 0, // Will be calculated after items
                'payment_method' => $request->payment_method ?? 'unknown',
                'status' => $request->status ?? 'pending',
            ]);

            // Create order items and deduct stock
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'] ?? null,
                    'qty' => $item['qty'],
                    'size' => $item['size'] ?? null,
                    'price' => $item['price'],
                ]);
                $totalAmount += $item['qty'] * $item['price'];

                // Deduct stock: user ordered qty, so reduce product stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock', (int) $item['qty']);
                }
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            // Load relationships for response
            $order->load('orderItems', 'user');

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'user_id' => $order->user_id,
                        'total_amount' => $order->total_amount,
                        'payment_method' => $order->payment_method,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'items' => $order->orderItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'product_name' => $item->product_name,
                                'qty' => $item->qty,
                                'size' => $item->size,
                                'price' => $item->price,
                                'total_price' => $item->total_price,
                            ];
                        }),
                    ]
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore product stock when an order is canceled or deleted
     */
    private function restoreStockForOrder(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', (int) $item->qty);
            }
        }
    }

    /**
     * Deduct product stock (e.g. when order is restored from canceled)
     */
    private function deductStockForOrder(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->decrement('stock', (int) $item->qty);
            }
        }
    }

    /**
     * Get all orders for a user
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $orders = Order::where('user_id', $request->user_id)
            ->with('orderItems')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'payment_method' => $order->payment_method,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'items_count' => $order->orderItems->count(),
                        'items' => $order->orderItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'product_name' => $item->product_name,
                                'qty' => $item->qty,
                                'size' => $item->size,
                                'price' => $item->price,
                                'total_price' => $item->total_price,
                            ];
                        }),
                    ];
                })
            ]
        ]);
    }

    /**
     * Get a specific order
     */
    public function show($id)
    {
        $order = Order::with('orderItems', 'user')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'user' => [
                        'id' => $order->user->id,
                        'username' => $order->user->username,
                        'name' => $order->user->name,
                    ],
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'qty' => $item->qty,
                            'size' => $item->size,
                            'price' => $item->price,
                            'total_price' => $item->total_price,
                        ];
                    }),
                ]
            ]
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,paid,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = Order::with('orderItems')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // If changing to canceled, restore stock; if changing from canceled to paid/pending, deduct stock again
        if ($request->status === 'canceled' && $order->status !== 'canceled') {
            $this->restoreStockForOrder($order);
        } elseif (in_array($request->status, ['pending', 'paid']) && $order->status === 'canceled') {
            $this->deductStockForOrder($order);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'updated_at' => $order->updated_at,
                ]
            ]
        ]);
    }

    /**
     * Delete an order
     */
    public function destroy($id)
    {
        $order = Order::with('orderItems')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Restore stock when order is deleted (unless already canceled, then stock was already restored)
        if ($order->status !== 'canceled') {
            $this->restoreStockForOrder($order);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }
}
