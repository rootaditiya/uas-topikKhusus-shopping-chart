<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\AddToCartJob;

class CartController extends Controller
{
    // Tambahkan produk ke keranjang
    public function addToCart(Request $request)
    {
        $productData = $request->all();

        // Kirim data ke message broker
        AddToCartJob::dispatch($productData);

        return response()->json([
            'message' => 'Product added to cart.',
            'product' => $productData,
        ]);
    }

    // Lihat isi keranjang
    public function viewCart($userId)
    {
        $cart = Cache::get('cart_' . $userId, []);

        return response()->json(['cart' => $cart]);
    }

    // Checkout keranjang
    public function checkout(Request $request)
{
    $userId = $request->input('user_id');
    $productsToCheckout = $request->input('products'); // ID produk yang akan dicheckout

    if (empty($productsToCheckout)) {
        return response()->json(['message' => 'No products selected for checkout.'], 400);
    }

    // Ambil data keranjang dari cache
    $cart = Cache::get('cart_' . $userId);

    if (!$cart) {
        return response()->json(['message' => 'Cart is empty.'], 400);
    }

    // Filter produk yang akan dicheckout
    $productsInCart = collect($cart)->keyBy('product_id');

    $productsToCheckoutDetails = [];
    $totalPrice = 0;

    foreach ($productsToCheckout as $productId) {
        if ($productsInCart->has($productId)) {
            $product = $productsInCart[$productId];
            $productsToCheckoutDetails[] = $product;
            $totalPrice += $product['price'] * $product['quantity']; // Hitung total harga

            // Hapus data produk yang akan dicheckout dari cache
            Cache::forget('cart_' . $userId . '_' . $productId);
        }
    }

    if (empty($productsToCheckoutDetails)) {
        return response()->json(['message' => 'No valid products selected for checkout.'], 400);
    }

    // Proses pembayaran (misalnya, melalui gateway pembayaran)
    // ...

    return response()->json([
        'message' => 'Checkout successful.',
        'products' => $productsToCheckoutDetails,
        'total_price' => $totalPrice, // Tambahkan total harga ke respons
    ]);
}

}
