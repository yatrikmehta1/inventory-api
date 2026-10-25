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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('slug', 180)->unique(); // Unique slugs make category URLs and lookups unambiguous.
            $table->boolean('is_active')->default(true)->index(); // Used to exclude inactive nodes.
            $table->timestamps();
            $table->index('parent_id'); // Supports child lookup for the adjacency-list tree.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
