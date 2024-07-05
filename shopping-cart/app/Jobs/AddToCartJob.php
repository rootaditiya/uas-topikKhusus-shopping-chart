<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AddToCartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $productData;

    public function __construct($productData)
    {
        $this->productData = $productData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         // Ambil data keranjang dari cache
         $cart = Cache::get('cart_' . $this->productData['user_id'], []);

         // Tambah produk ke keranjang
         $cart[] = $this->productData;
 
         // Simpan kembali ke cache
         Cache::put('cart_' . $this->productData['user_id'], $cart, 3600);

    }
}
