<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private const TAX_RATE = 0.05;

    public function index()
    {
        $cart = $this->getCart();
        $items = array_values($cart);
        $totals = $this->calculateTotals($items);

        return view('frontend.cart', [
            'items' => $items,
            'subTotal' => $totals['subTotal'],
            'discount' => $totals['discount'],
            'tax' => $totals['tax'],
            'shipping' => $totals['shipping'],
            'grandTotal' => $totals['grandTotal'],
            'itemCount' => $totals['itemCount'],
            'coupon' => $totals['coupon'],
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = $this->getCart();

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $price = $this->productPrice($product);
            $imagePath = $this->productImagePath($product);
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $price,
                'image_path' => $imagePath,
                'image_url' => $this->productImageUrl($imagePath),
                'quantity' => $quantity,
            ];
        }

        $this->putCart($cart);

        $action = $request->input('action', 'cart');
        if ($action === 'checkout') {
            return redirect()->route('checkout');
        }

        return redirect()->route('cart')->with('success', 'Added to cart.');
    }

    public function update(Request $request)
    {
        $quantities = $request->input('quantities', []);
        $cart = $this->getCart();

        foreach ($quantities as $productId => $qty) {
            if (!isset($cart[$productId])) {
                continue;
            }

            $qty = (int) $qty;
            if ($qty <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $qty;
            }
        }

        $this->putCart($cart);

        if ($request->input('action') === 'checkout') {
            return redirect()->route('checkout');
        }

        return redirect()->route('cart')->with('success', 'Cart updated.');
    }

    public function remove(Product $product)
    {
        $cart = $this->getCart();
        unset($cart[$product->id]);
        $this->putCart($cart);

        return redirect()->route('cart')->with('success', 'Item removed.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->status) {
            return redirect()->back()->withInput()->with('error', 'Invalid or inactive coupon code.');
        }

        session()->put('coupon_id', $coupon->id);

        $cart = $this->getCart();
        $items = array_values($cart);
        $totals = $this->calculateTotals($items);

        if (!$totals['coupon']) {
            session()->forget('coupon_id');
            return redirect()->back()->withInput()->with('error', 'Coupon is not applicable to your cart.');
        }

        return redirect()->back()->withInput()->with('success', 'Coupon applied successfully.');
    }

    public function removeCoupon()
    {
        session()->forget('coupon_id');
        return redirect()->back()->withInput()->with('success', 'Coupon removed.');
    }

    public function checkout()
    {
        $cart = $this->getCart();
        $items = array_values($cart);

        if (count($items) === 0) {
            return redirect()->route('shop')->with('error', 'Your cart is empty.');
        }

        $totals = $this->calculateTotals($items);
        $addresses = auth()->check() ? auth()->user()->addresses : collect();

        return view('frontend.checkout', [
            'items' => $items,
            'subTotal' => $totals['subTotal'],
            'discount' => $totals['discount'],
            'tax' => $totals['tax'],
            'shipping' => $totals['shipping'],
            'grandTotal' => $totals['grandTotal'],
            'itemCount' => $totals['itemCount'],
            'coupon' => $totals['coupon'],
            'addresses' => $addresses,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $cart = $this->getCart();
        $items = array_values($cart);

        if (count($items) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $totals = $this->calculateTotals($items);
        $coupon = $totals['coupon'];

        if ($coupon) {
            if ($coupon->first_order_only) {
                $hasOrders = Order::where('customer_email', $request->input('customer_email'))->exists();
                if ($hasOrders) {
                    session()->forget('coupon_id');
                    return redirect()->route('checkout')->with('error', 'This coupon is valid for first-time orders only.');
                }
            }

            if ($coupon->per_user_limit) {
                $usedCount = Order::where('coupon_id', $coupon->id)
                    ->where('customer_email', $request->input('customer_email'))
                    ->count();
                if ($usedCount >= $coupon->per_user_limit) {
                    session()->forget('coupon_id');
                    return redirect()->route('checkout')->with('error', 'You have reached the usage limit for this coupon.');
                }
            }
        }

        $isDifferentBilling = !$request->has('same_as_shipping');
        
        $order = Order::create([
            'user_id' => auth()->id(),
            'coupon_id' => $coupon ? $coupon->id : null,
            'coupon_code' => $coupon ? $coupon->code : null,
            'customer_name' => $request->input('customer_name'),
            'customer_email' => $request->input('customer_email'),
            'customer_phone' => $request->input('customer_phone'),
            'billing_name' => $isDifferentBilling ? $request->input('billing_name') : $request->input('customer_name'),
            'billing_email' => $isDifferentBilling ? $request->input('billing_email') : $request->input('customer_email'),
            'billing_phone' => $isDifferentBilling ? $request->input('billing_phone') : $request->input('customer_phone'),
            'different_billing_address' => $isDifferentBilling,
            'sub_total' => $totals['subTotal'],
            'discount' => $totals['discount'],
            'tax' => $totals['tax'],
            'shipping' => $totals['shipping'],
            'grand_total' => $totals['grandTotal'],
            'payment_method' => $request->input('payment_method', 'cod'),
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'delivery_address' => $request->input('delivery_address'),
            'billing_address' => $isDifferentBilling ? $request->input('billing_address') : $request->input('delivery_address'),
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'total' => $item['price'] * $item['quantity'],
            ]);
        }

        session()->forget('cart');
        session()->forget('coupon_id');

        if ($coupon) {
            $coupon->increment('times_used');
        }

        return redirect()->route('order-confirmation', $order);
    }

    public function orderConfirmation(Order $order = null)
    {
        if ($order) {
            $order->load('items.product');
        }

        return view('frontend.order-confirmation', [
            'order' => $order,
        ]);
    }

    private function getCart(): array
    {
        return session()->get('cart', []);
    }

    private function putCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    private function calculateTotals(array $items): array
    {
        $subTotal = 0;
        $itemCount = 0;

        foreach ($items as $item) {
            $subTotal += $item['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        $subTotal = round($subTotal, 2);
        $shipping = 0;
        $couponResult = $this->resolveCoupon($items, $subTotal);
        $discount = $couponResult['discount'];
        $coupon = $couponResult['coupon'];
        $taxableAmount = max(0, $subTotal - $discount);
        $tax = round($taxableAmount * self::TAX_RATE, 2);
        $grandTotal = round($taxableAmount + $tax + $shipping, 2);

        return compact('subTotal', 'discount', 'tax', 'shipping', 'grandTotal', 'itemCount', 'coupon');
    }

    private function productPrice(Product $product): float
    {
        if (!is_null($product->sale_price) && $product->sale_price > 0) {
            return (float) $product->sale_price;
        }
        if (!is_null($product->regular_price) && $product->regular_price > 0) {
            return (float) $product->regular_price;
        }
        if (!is_null($product->price)) {
            return (float) $product->price;
        }

        return 0.0;
    }

    private function productImagePath(Product $product): ?string
    {
        if (!empty($product->image_path)) {
            return $product->image_path;
        }

        $images = $product->images;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (is_array($images) && count($images) > 0) {
            return $images[0];
        }

        return null;
    }

    private function productImageUrl(?string $imagePath): string
    {
        if (!$imagePath) {
            return asset('images/pro.png');
        }

        if (Str::startsWith($imagePath, 'images/')) {
            return asset($imagePath);
        }

        if (Str::startsWith($imagePath, 'products/') || Str::startsWith($imagePath, 'categories/')) {
            return asset('uploads/' . $imagePath);
        }

        return asset('images/' . $imagePath);
    }

    private function resolveCoupon(array $items, float $subTotal): array
    {
        $couponId = session()->get('coupon_id');
        if (!$couponId) {
            return ['coupon' => null, 'discount' => 0];
        }

        $coupon = Coupon::find($couponId);
        if (!$coupon || !$coupon->status) {
            return $this->invalidateCoupon();
        }

        $now = now();
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return $this->invalidateCoupon();
        }
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return $this->invalidateCoupon();
        }
        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return $this->invalidateCoupon();
        }
        if ($coupon->min_order_amount && $subTotal < $coupon->min_order_amount) {
            return $this->invalidateCoupon();
        }

        $eligibleSubtotal = $this->eligibleSubtotal($items, $coupon);
        if ($eligibleSubtotal <= 0) {
            return $this->invalidateCoupon();
        }

        if ($coupon->type === 'percentage') {
            $discount = $eligibleSubtotal * ($coupon->discount_value / 100);
        } else {
            $discount = $coupon->discount_value;
        }

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        $discount = min($discount, $eligibleSubtotal);

        return [
            'coupon' => $coupon,
            'discount' => round($discount, 2),
        ];
    }

    private function eligibleSubtotal(array $items, Coupon $coupon): float
    {
        $productIds = $coupon->applicable_products ?? [];
        $categoryIds = $coupon->applicable_categories ?? [];

        $sum = 0;

        if (empty($productIds) && empty($categoryIds)) {
            foreach ($items as $item) {
                $sum += $item['price'] * $item['quantity'];
            }
            return round($sum, 2);
        }

        $itemProductIds = array_column($items, 'product_id');
        $productCategories = Product::whereIn('id', $itemProductIds)->pluck('category_id', 'id')->toArray();

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $categoryId = $productCategories[$productId] ?? null;

            if (in_array($productId, $productIds, false) || ($categoryId && in_array($categoryId, $categoryIds, false))) {
                $sum += $item['price'] * $item['quantity'];
            }
        }

        return round($sum, 2);
    }

    private function invalidateCoupon(): array
    {
        session()->forget('coupon_id');
        return ['coupon' => null, 'discount' => 0];
    }
}
