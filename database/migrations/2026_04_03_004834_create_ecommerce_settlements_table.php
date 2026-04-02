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
        Schema::create('ecommerce_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_integration_id')->constrained()->cascadeOnDelete();
            $table->string('settlement_id')->unique(); // ID unik dari platform TikTok
            $table->string('platform_order_id')->nullable(); // Terkait order ID apa (opsional untuk settlement level toko)
            $table->decimal('settlement_amount', 15, 2)->default(0);
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->string('currency')->default('IDR');
            $table->timestamp('settlement_time')->nullable();
            $table->foreignId('cash_transaction_id')->nullable()->constrained()->nullOnDelete(); // Terhubung langsung ke Buku Kas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_settlements');
    }
};
