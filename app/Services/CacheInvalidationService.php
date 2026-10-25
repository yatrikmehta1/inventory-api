<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheInvalidationService
{
    public function stockChanged(): void
    {
        Cache::forget('inventory:summary:v1');
        // Versioned namespace invalidates all product list combinations without enumerating filter hashes.
        $version = (int) Cache::get('inventory:products:version', 1);
        Cache::forever('inventory:products:version', $version + 1);
        $version = (int) Cache::get('inventory:low-stock:version', 1);
        Cache::forever('inventory:low-stock:version', $version + 1);
    }

    public function categoriesChanged(): void
    {
        Cache::forget('inventory:categories:tree:v1');
        $version = (int) Cache::get('inventory:products:version', 1);
        Cache::forever('inventory:products:version', $version + 1);
    }

    public function productsChanged(): void
    {
        $version = (int) Cache::get('inventory:products:version', 1);
        Cache::forever('inventory:products:version', $version + 1);
        Cache::forget('inventory:summary:v1');
    }
}
