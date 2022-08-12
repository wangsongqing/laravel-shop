<?php

namespace App\Observers;

use App\Jobs\SyncOneProductToES;
use App\Models\Product;
use App\Models\ProductSku;

class ProductSkuObserver
{
    /**
     * Handle the ProductSku "created" event.
     *
     * @param  \App\Models\ProductSku  $productSku
     * @return void
     */
    public function created(ProductSku $productSku)
    {
        $product = Product::query()->find($productSku->product_id);
        dispatch(new SyncOneProductToES($product));
    }

    /**
     * Handle the ProductSku "updated" event.
     *
     * @param  \App\Models\ProductSku  $productSku
     * @return void
     */
    public function updated(ProductSku $productSku)
    {
        $product = Product::query()->find($productSku->product_id);
        dispatch(new SyncOneProductToES($product));
    }

    /**
     * Handle the ProductSku "deleted" event.
     *
     * @param  \App\Models\ProductSku  $productSku
     * @return void
     */
    public function deleted(ProductSku $productSku)
    {
        //
    }

    /**
     * Handle the ProductSku "restored" event.
     *
     * @param  \App\Models\ProductSku  $productSku
     * @return void
     */
    public function restored(ProductSku $productSku)
    {
        //
    }

    /**
     * Handle the ProductSku "force deleted" event.
     *
     * @param  \App\Models\ProductSku  $productSku
     * @return void
     */
    public function forceDeleted(ProductSku $productSku)
    {
        //
    }
}
