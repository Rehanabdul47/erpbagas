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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('supplier_product_id')->nullable()->after('product_id')->constrained('supplier_products')->nullOnDelete();
            
            // Note: Since SQLite on development might not support altering columns directly without doctrine/dbal,
            // and maybe product_id was required, we just make supplier_product_id nullable to avoid conflict during migration.
            // But we will populate supplier_product_id going forward.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            //
        });
    }
};
