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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('movement_type', 30);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type', 120)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamp('moved_at')->useCurrent();
            $table->index(['product_id', 'moved_at']); // Product history and date-range summaries.
            $table->index(['warehouse_id', 'moved_at']); // Warehouse reporting.
            $table->index(['movement_type', 'moved_at']); // Type-specific period aggregation.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
