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
        Schema::create('ecommerce_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ecommerce_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform_product_id');
            $table->string('platform_sku_id')->nullable();
            $table->string('product_name');
            $table->string('sku_name')->nullable();
            $table->integer('quantity');
            $table->decimal('original_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('platform_discount', 15, 2)->default(0);
            $table->decimal('seller_discount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_order_items');
    }
};
