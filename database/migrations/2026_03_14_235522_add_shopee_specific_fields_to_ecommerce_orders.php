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
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->decimal('subtotal_product', 15, 2)->default(0)->after('total_amount');
            $table->decimal('shipping_fee_buyer_paid', 15, 2)->default(0)->after('subtotal_product');
            $table->decimal('shipping_fee_discount', 15, 2)->default(0)->after('shipping_fee_buyer_paid');
            $table->decimal('voucher_platform', 15, 2)->default(0)->after('shipping_fee_discount');
            $table->decimal('voucher_seller', 15, 2)->default(0)->after('voucher_platform');
            $table->decimal('admin_fee', 15, 2)->default(0)->after('voucher_seller');
            $table->decimal('processing_fee', 15, 2)->default(0)->after('admin_fee');
            $table->decimal('total_income', 15, 2)->default(0)->after('processing_fee');
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_product',
                'shipping_fee_buyer_paid',
                'shipping_fee_discount',
                'voucher_platform',
                'voucher_seller',
                'admin_fee',
                'service_fee',
                'processing_fee',
                'total_income',
                'notes'
            ]);
        });
    }
};
