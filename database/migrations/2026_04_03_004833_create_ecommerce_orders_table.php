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
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_integration_id')->constrained()->cascadeOnDelete();
            $table->string('platform_order_id')->unique(); // TikTok Order ID
            $table->string('status'); // UNPAID, AWAITING_SHIPMENT, AWAITING_COLLECTION, IN_TRANSIT, DELIVERED, COMPLETED, CANCELLED
            $table->string('payment_method')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('platform_discount', 15, 2)->default(0);
            $table->decimal('seller_discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('buyer_message')->nullable();
            
            // Shipping Info
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('full_address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->string('zipcode')->nullable();
            
            // Logistics
            $table->string('shipping_provider')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('shipping_document_url')->nullable(); // Awb document URL/Pdf base64
            
            // Sync Status flag (for updating local inventory)
            $table->boolean('is_stock_deducted')->default(false);
            
            $table->timestamp('order_created_at')->nullable();
            $table->timestamp('order_paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_orders');
    }
};
