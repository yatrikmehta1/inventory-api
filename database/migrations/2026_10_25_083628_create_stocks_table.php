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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']); // One balance per product/warehouse; also supports exact pair lookups.
            $table->index(['warehouse_id', 'quantity', 'reserved_quantity']); // Helps warehouse stock/availability filtering.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
