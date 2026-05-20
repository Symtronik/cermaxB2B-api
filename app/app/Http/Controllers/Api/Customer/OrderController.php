<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Order::query()
            ->with(['shippingPoint'])
            ->withCount('items')
            ->latest();

        if ($user->hasRole('customer')) {
            $query->where('user_id', $user->id);
        }

        if (($user->hasRole('admin') || $user->hasRole('super_admin')) && $request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        return response()->json([
            'data' => $query->paginate(20),
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $order->load([
            'items',
            'shippingPoint',
            'user',
        ]);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function repeatPreview(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $order->load('items');

        $items = $order->items->map(function ($item) {
            $product = Product::query()->find($item->product_id);

            if (! $product || ! $product->is_active) {
                return [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'sku' => $item->sku,

                    'quantity' => (int) $item->quantity,
                    'pack_qty' => (int) $item->pack_qty,
                    'pieces_total' => (int) $item->pieces_total,

                    'old_net_pack' => $item->net_pack,
                    'old_gross_pack' => $item->gross_pack,
                    'old_net_total' => $item->net_total,
                    'old_gross_total' => $item->gross_total,

                    'current_net_pack' => null,
                    'current_gross_pack' => null,
                    'current_net_total' => null,
                    'current_gross_total' => null,

                    'available' => false,
                    'available_packs' => 0,
                    'available_pieces' => 0,
                    'message' => 'Produkt nie jest już dostępny.',
                ];
            }

            $quantity = max(1, (int) $item->quantity);
            $packQty = max(1, (int) $product->pack_qty);
            $stockPieces = max(0, (int) $product->stock_qty);
            $availablePacks = intdiv($stockPieces, $packQty);

            $currentNetPack = $this->netPackPrice($product);
            $currentGrossPack = $this->grossPackPrice($product);

            return [
                'order_item_id' => $item->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,

                'quantity' => $quantity,
                'pack_qty' => $packQty,
                'pieces_total' => $quantity * $packQty,

                'old_net_pack' => $item->net_pack,
                'old_gross_pack' => $item->gross_pack,
                'old_net_total' => $item->net_total,
                'old_gross_total' => $item->gross_total,

                'current_net_pack' => $currentNetPack,
                'current_gross_pack' => $currentGrossPack,
                'current_net_total' => round($currentNetPack * $quantity, 2),
                'current_gross_total' => round($currentGrossPack * $quantity, 2),

                'available' => $availablePacks >= $quantity,
                'available_packs' => $availablePacks,
                'available_pieces' => $stockPieces,
                'message' => $availablePacks >= $quantity
                    ? 'Produkt dostępny.'
                    : 'Brak wymaganej ilości. Można dodać mniejszą ilość.',
            ];
        });

        return response()->json([
            'data' => [
                'order' => $order,
                'items' => $items,
            ],
        ]);
    }

    public function repeatToCart(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $cart = Cart::query()
                ->firstOrCreate([
                    'user_id' => $request->user()->id,
                    'status' => 'active',
                ]);

            $added = [];
            $skipped = [];

            foreach ($data['items'] as $row) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->find($row['product_id']);

                if (! $product || ! $product->is_active) {
                    $skipped[] = [
                        'product_id' => $row['product_id'],
                        'message' => 'Produkt nie jest dostępny.',
                    ];
                    continue;
                }

                $packQty = max(1, (int) $product->pack_qty);
                $quantity = max(1, (int) $row['quantity']);
                $piecesTotal = $quantity * $packQty;
                $stockPieces = max(0, (int) $product->stock_qty);

                if ($piecesTotal > $stockPieces) {
                    $skipped[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'available_packs' => intdiv($stockPieces, $packQty),
                        'message' => 'Brak wystarczającej ilości.',
                    ];
                    continue;
                }

                $cartItem = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->update([
                        'quantity' => $cartItem->quantity + $quantity,
                    ]);
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                    ]);
                }

                $added[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'pack_qty' => $packQty,
                    'net_pack' => $this->netPackPrice($product),
                    'gross_pack' => $this->grossPackPrice($product),
                ];
            }

            return response()->json([
                'message' => 'Dostępne produkty zostały dodane do koszyka.',
                'data' => [
                    'added' => $added,
                    'skipped' => $skipped,
                ],
            ]);
        });
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();

        if ($user->hasRole('customer')) {
            abort_unless((int) $order->user_id === (int) $user->id, 403);
        }
    }

    private function netPackPrice(Product $product): float
    {
        $netPack = (float) ($product->net_pack ?? 0);

        if ($netPack > 0) {
            return round($netPack, 2);
        }

        $netUnit = (float) ($product->net_unit ?? 0);
        $packQty = max(1, (int) ($product->pack_qty ?? 1));

        return round($netUnit * $packQty, 2);
    }

    private function grossPackPrice(Product $product): float
    {
        $grossPack = (float) ($product->gross_pack ?? 0);

        if ($grossPack > 0) {
            return round($grossPack, 2);
        }

        $grossUnit = (float) ($product->gross_unit ?? 0);
        $packQty = max(1, (int) ($product->pack_qty ?? 1));

        if ($grossUnit > 0) {
            return round($grossUnit * $packQty, 2);
        }

        $net = $this->netPackPrice($product);
        $vat = (float) ($product->vat_rate ?? 23);

        return round($net * (1 + ($vat / 100)), 2);
    }
}
