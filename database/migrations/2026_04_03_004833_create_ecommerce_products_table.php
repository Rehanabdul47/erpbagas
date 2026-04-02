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
        Schema::create('ecommerce_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_integration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // Map to local product
            $table->string('platform_product_id'); // e.g. TikTok product_id
            $table->string('platform_sku_id')->nullable(); // e.g. TikTok sku_id
            $table->string('platform_product_name')->nullable();
            $table->unique(['ecommerce_integration_id', 'platform_product_id', 'platform_sku_id'], 'ecom_prod_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_products');
    }
};
