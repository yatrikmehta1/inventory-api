<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 180);
            $table->string('sku', 80)->unique(); // SKU must be unique and searchable.
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2); // Exact decimal representation for currency.
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['category_id', 'is_active']); // Common category + active listing filter.
            $table->index('base_price'); // Supports min/max price filters.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
