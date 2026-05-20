<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingPoint;


class CartController extends Controller
{
    private function activeCart(Request $request): Cart
    {
        return Cart::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'status' => 'active',
            ],
            [
                'expires_at' => now()->addDays(14),
            ]
        );
    }

    public function show(Request $request)
    {
        $cart = $this->activeCart($request);

        $cart->load([
            'items.product.images',
            'items.product.attributes',
        ]);

        return response()->json([
            'data' => [
                'id' => $cart->id,
                'status' => $cart->status,
                'expires_at' => $cart->expires_at,
                'items' => $cart->items->map(function (CartItem $item) {
                    return $this->formatItem($item);
                })->values(),
            ],
        ]);
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $cart = $this->activeCart($request);

            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            if (! $product->is_active) {
                return response()->json([
                    'message' => 'Ten produkt nie jest już dostępny.',
                ], 422);
            }

            $packQty = max(1, (int) $product->pack_qty);
            $requestedPacks = (int) $data['quantity'];
            $requestedPieces = $requestedPacks * $packQty;
            $stockPieces = (int) $product->stock_qty;

            if ($requestedPieces > $stockPieces) {
                return response()->json([
                    'message' => 'Brak wystarczającej ilości produktu na stanie.',
                    'available_packs' => intdiv($stockPieces, $packQty),
                    'available_pieces' => $stockPieces,
                ], 422);
            }

            $netPack = $this->netPackPrice($product);
            $grossPack = $this->grossPackPrice($product);

            $item = CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $requestedPacks,
                    'net_pack_snapshot' => $netPack,
                    'gross_pack_snapshot' => $grossPack,
                ]
            );

            $item->load('product.images', 'product.attributes');

            return response()->json([
                'message' => 'Produkt dodany do koszyka.',
                'data' => $this->formatItem($item),
            ]);
        });
    }

    public function updateItem(Request $request, CartItem $item)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($request, $item, $data) {
            $cart = $this->activeCart($request);

            if ($item->cart_id !== $cart->id) {
                abort(404);
            }

            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($item->product_id);

            if (! $product->is_active) {
                return response()->json([
                    'message' => 'Ten produkt nie jest już dostępny.',
                ], 422);
            }

            $packQty = max(1, (int) $product->pack_qty);
            $requestedPacks = (int) $data['quantity'];
            $requestedPieces = $requestedPacks * $packQty;
            $stockPieces = (int) $product->stock_qty;

            if ($requestedPieces > $stockPieces) {
                return response()->json([
                    'message' => 'Brak wystarczającej ilości produktu na stanie.',
                    'available_packs' => intdiv($stockPieces, $packQty),
                    'available_pieces' => $stockPieces,
                ], 422);
            }

            $item->update([
                'quantity' => $requestedPacks,
                'net_pack_snapshot' => $this->netPackPrice($product),
                'gross_pack_snapshot' => $this->grossPackPrice($product),
            ]);

            $item->load('product.images', 'product.attributes');

            return response()->json([
                'message' => 'Koszyk zaktualizowany.',
                'data' => $this->formatItem($item),
            ]);
        });
    }

    public function destroyItem(Request $request, CartItem $item)
    {
        $cart = $this->activeCart($request);

        if ($item->cart_id !== $cart->id) {
            abort(404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Produkt usunięty z koszyka.',
        ]);
    }

    public function clear(Request $request)
    {
        $cart = $this->activeCart($request);

        $cart->items()->delete();

        return response()->json([
            'message' => 'Koszyk wyczyszczony.',
        ]);
    }

    public function validateCart(Request $request)
    {
        $cart = $this->activeCart($request);

        $cart->load('items.product.images');

        $errors = [];

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active) {
                $errors[] = [
                    'cart_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'message' => 'Produkt nie jest już dostępny.',
                ];

                continue;
            }

            $packQty = max(1, (int) $product->pack_qty);
            $neededPieces = $item->quantity * $packQty;
            $stockPieces = (int) $product->stock_qty;

            if ($neededPieces > $stockPieces) {
                $errors[] = [
                    'cart_item_id' => $item->id,
                    'product_id' => $product->id,
                    'message' => 'Brak wystarczającej ilości produktu.',
                    'requested_packs' => $item->quantity,
                    'available_packs' => intdiv($stockPieces, $packQty),
                    'available_pieces' => $stockPieces,
                ];
            }
        }

        return response()->json([
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ]);
    }

    private function formatItem(CartItem $item): array
    {
        $product = $item->product;

        if (! $product) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'available' => false,
                'message' => 'Produkt nie istnieje.',
            ];
        }

        $packQty = max(1, (int) $product->pack_qty);
        $stockPieces = (int) $product->stock_qty;
        $availablePacks = intdiv($stockPieces, $packQty);
        $requestedPieces = $item->quantity * $packQty;

        $netPack = $this->netPackPrice($product);
        $grossPack = $this->grossPackPrice($product);

        return [
            'id' => $item->id,
            'product_id' => $product->id,
            'quantity' => $item->quantity,
            'quantity_label' => 'Ilość opakowań',
            'pack_qty' => $packQty,
            'pieces_total' => $requestedPieces,
            'available' => (bool) $product->is_active && $requestedPieces <= $stockPieces,
            'available_packs' => $availablePacks,
            'available_pieces' => $stockPieces,
            'net_pack' => $netPack,
            'gross_pack' => $grossPack,
            'net_total' => round($netPack * $item->quantity, 2),
            'gross_total' => round($grossPack * $item->quantity, 2),
            'product' => $product,
        ];
    }

    private function netPackPrice(Product $product): float
    {
        $netPack = (float) $product->net_pack;

        if ($netPack > 0) {
            return round($netPack, 2);
        }

        return round((float) $product->net_unit * max(1, (int) $product->pack_qty), 2);
    }

    private function grossPackPrice(Product $product): float
    {
        $grossPack = (float) $product->gross_pack;

        if ($grossPack > 0) {
            return round($grossPack, 2);
        }

        return round((float) $product->gross_unit * max(1, (int) $product->pack_qty), 2);
    }

    public function checkout(Request $request)
{
    $data = $request->validate([
        'customer_note' => ['nullable', 'string', 'max:2000'],
        'shipping_point_id' => ['required', 'integer', 'exists:shipping_points,id'],
    ]);

    return DB::transaction(function () use ($request, $data) {
        $cart = $this->activeCart($request);

        $shippingPoint = ShippingPoint::query()
            ->where('id', $data['shipping_point_id'])
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $shippingPoint) {
            return response()->json([
                'message' => 'Wybrany punkt wysyłki jest nieprawidłowy.',
            ], 422);
        }

        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Koszyk jest pusty.',
            ], 422);
        }

        $netTotal = 0;
        $grossTotal = 0;
        $preparedItems = [];

        foreach ($cart->items as $item) {
            $product = Product::query()
                ->lockForUpdate()
                ->find($item->product_id);

            if (! $product || ! $product->is_active) {
                return response()->json([
                    'message' => 'Jeden z produktów nie jest już dostępny.',
                    'product_id' => $item->product_id,
                ], 422);
            }

            $packQty = max(1, (int) $product->pack_qty);
            $quantity = max(1, (int) $item->quantity);
            $piecesTotal = $quantity * $packQty;
            $stockPieces = (int) $product->stock_qty;

            if ($piecesTotal > $stockPieces) {
                return response()->json([
                    'message' => 'Brak wystarczającej ilości produktu na stanie.',
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'requested_packs' => $quantity,
                    'requested_pieces' => $piecesTotal,
                    'available_packs' => intdiv($stockPieces, $packQty),
                    'available_pieces' => $stockPieces,
                ], 422);
            }

            $netPack = $this->netPackPrice($product);
            $grossPack = $this->grossPackPrice($product);

            $lineNetTotal = round($netPack * $quantity, 2);
            $lineGrossTotal = round($grossPack * $quantity, 2);

            $netTotal += $lineNetTotal;
            $grossTotal += $lineGrossTotal;

            $preparedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'pack_qty' => $packQty,
                'pieces_total' => $piecesTotal,
                'net_pack' => $netPack,
                'gross_pack' => $grossPack,
                'net_total' => $lineNetTotal,
                'gross_total' => $lineGrossTotal,
            ];
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'cart_id' => $cart->id,
            'shipping_point_id' => $shippingPoint->id,
            'number' => $this->generateOrderNumber(),
            'status' => 'new',
            'net_total' => round($netTotal, 2),
            'gross_total' => round($grossTotal, 2),
            'customer_note' => $data['customer_note'] ?? null,
        ]);

        foreach ($preparedItems as $prepared) {
            $product = $prepared['product'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'ean' => $product->ean,
                'quantity' => $prepared['quantity'],
                'pack_qty' => $prepared['pack_qty'],
                'pieces_total' => $prepared['pieces_total'],
                'net_pack' => $prepared['net_pack'],
                'gross_pack' => $prepared['gross_pack'],
                'net_total' => $prepared['net_total'],
                'gross_total' => $prepared['gross_total'],
            ]);

            $product->decrement('stock_qty', $prepared['pieces_total']);
        }

        $cart->update([
            'status' => 'converted',
        ]);

        $order->load(['items', 'shippingPoint']);

        return response()->json([
            'message' => 'Zamówienie zostało zapisane.',
            'data' => $order,
        ], 201);
    });
}
    private function generateOrderNumber(): string
    {
        $prefix = 'ZAM/' . now()->format('Y/m/d');

        $countToday = Order::query()
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $prefix . '/' . str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);
    }
}
