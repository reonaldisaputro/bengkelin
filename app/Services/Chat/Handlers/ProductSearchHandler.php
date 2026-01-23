<?php

namespace App\Services\Chat\Handlers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\Chat\ContextManager;
use App\Services\Chat\ResponseBuilder;
use Illuminate\Support\Str;

class ProductSearchHandler
{
    private ContextManager $contextManager;

    public function __construct(ContextManager $contextManager)
    {
        $this->contextManager = $contextManager;
    }

    /**
     * Handle product search prompt
     */
    public function handle(User $user): array
    {
        return ResponseBuilder::make()
            ->text('Ketik nama produk yang ingin Anda cari.')
            ->text('Contoh: cari oli mesin')
            ->quickReplies([
                ['title' => 'Lihat Semua Produk', 'payload' => 'product_list'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle product search with keyword
     */
    public function handleSearch(User $user, string $keyword, int $limit = 5): array
    {
        $products = Product::with('bengkel')
            ->where('name', 'LIKE', '%' . $keyword . '%')
            ->where('stock', '>', 0)
            ->limit($limit)
            ->get();

        // Save search context
        $this->contextManager->updateSearchContext(
            $user->id,
            $keyword,
            $products->pluck('id')->toArray()
        );

        if ($products->isEmpty()) {
            return ResponseBuilder::make()
                ->text("Produk tidak ditemukan untuk \"{$keyword}\".")
                ->text('Coba kata kunci lain atau lihat semua produk.')
                ->quickReplies([
                    ['title' => 'Cari Lagi', 'payload' => 'product_search_prompt'],
                    ['title' => 'Lihat Semua', 'payload' => 'product_list'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text("Ditemukan {$products->count()} produk untuk \"{$keyword}\":");

        foreach ($products as $product) {
            $builder->productCard(
                $product->id,
                $product->name,
                $product->price,
                $product->bengkel?->name ?? 'Bengkel',
                $product->stock,
                $product->image ? url('storage/' . $product->image) : null
            );
        }

        $builder->quickReplies([
            ['title' => 'Cari Lagi', 'payload' => 'product_search_prompt'],
            ['title' => 'Menu Utama', 'payload' => 'menu'],
        ]);

        return $builder
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle add to cart
     */
    public function handleAddToCart(User $user, int $productId): array
    {
        $product = Product::with('bengkel')->find($productId);

        if (!$product) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.product_not_found'))
                ->menuQuickReply()
                ->context((string) $user->id)
                ->build();
        }

        if ($product->stock <= 0) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.out_of_stock'))
                ->quickReplies([
                    ['title' => 'Cari Produk Lain', 'payload' => 'product_search_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        // Check if cart has items from different bengkel
        $existingCart = Cart::where('user_id', $user->id)
            ->whereHas('product', function ($q) use ($product) {
                $q->where('bengkel_id', '!=', $product->bengkel_id);
            })
            ->first();

        if ($existingCart) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.cart_bengkel_conflict'))
                ->text("Produk di keranjang dari bengkel lain. Checkout atau kosongkan keranjang dulu.")
                ->quickReplies([
                    ['title' => 'Lihat Keranjang', 'payload' => 'view_cart'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        // Check if product already in cart
        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->increment('qty');
            $message = "Jumlah {$product->name} di keranjang ditambah menjadi {$cartItem->qty}.";
        } else {
            Cart::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'bengkel_id' => $product->bengkel_id,
                'qty' => 1,
            ]);
            $message = "{$product->name} berhasil ditambahkan ke keranjang!";
        }

        return ResponseBuilder::make()
            ->text($message)
            ->text("Harga: Rp " . number_format($product->price, 0, ',', '.'))
            ->quickReplies([
                ['title' => 'Lihat Keranjang', 'payload' => 'view_cart'],
                ['title' => 'Cari Produk Lain', 'payload' => 'product_search_prompt'],
                ['title' => 'Checkout', 'payload' => 'checkout'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle view cart
     */
    public function handleViewCart(User $user): array
    {
        $cartItems = Cart::with('product.bengkel')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return ResponseBuilder::make()
                ->text('Keranjang Anda kosong.')
                ->quickReplies([
                    ['title' => 'Cari Produk', 'payload' => 'product_search_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        $total = 0;
        $cartText = "🛒 *Keranjang Belanja*\n\n";

        foreach ($cartItems as $item) {
            $subtotal = $item->product->price * $item->qty;
            $total += $subtotal;
            $cartText .= "• {$item->product->name}\n";
            $cartText .= "  {$item->qty} x Rp " . number_format($item->product->price, 0, ',', '.');
            $cartText .= " = Rp " . number_format($subtotal, 0, ',', '.') . "\n";
        }

        $cartText .= "\n💰 *Total: Rp " . number_format($total, 0, ',', '.') . "*";

        $bengkelName = $cartItems->first()->product->bengkel?->name ?? 'Bengkel';
        $cartText .= "\n🏪 Dari: {$bengkelName}";

        return ResponseBuilder::make()
            ->text($cartText)
            ->quickReplies([
                ['title' => 'Checkout', 'payload' => 'checkout'],
                ['title' => 'Tambah Produk', 'payload' => 'product_search_prompt'],
                ['title' => 'Kosongkan Keranjang', 'payload' => 'clear_cart'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle product list (all products)
     */
    public function handleList(User $user, int $page = 1, int $perPage = 5): array
    {
        $products = Product::with('bengkel')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = Product::where('stock', '>', 0)->count();
        $totalPages = ceil($total / $perPage);

        if ($products->isEmpty()) {
            return ResponseBuilder::make()
                ->text('Tidak ada produk yang tersedia.')
                ->menuQuickReply()
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text("Daftar Produk (Halaman {$page} dari {$totalPages}):");

        foreach ($products as $product) {
            $builder->productCard(
                $product->id,
                $product->name,
                $product->price,
                $product->bengkel?->name ?? 'Bengkel',
                $product->stock,
                $product->image ? url('storage/' . $product->image) : null
            );
        }

        // Pagination
        $replies = [];
        if ($page > 1) {
            $replies[] = ['title' => '⬅️ Sebelumnya', 'payload' => 'product_list_page_' . ($page - 1)];
        }
        if ($page < $totalPages) {
            $replies[] = ['title' => 'Selanjutnya ➡️', 'payload' => 'product_list_page_' . ($page + 1)];
        }
        $replies[] = ['title' => 'Cari Produk', 'payload' => 'product_search_prompt'];
        $replies[] = ['title' => 'Menu Utama', 'payload' => 'menu'];

        $builder->quickReplies($replies);

        return $builder
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Extract search keyword from message
     */
    public function extractKeyword(string $message): ?string
    {
        $patterns = [
            '/^cari\s+(.+)/i',
            '/^produk\s+(.+)/i',
            '/^beli\s+(.+)/i',
            '/^spare\s*part\s+(.+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message), $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }
}
