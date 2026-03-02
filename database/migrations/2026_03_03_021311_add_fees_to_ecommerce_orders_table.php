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
            $table->decimal('platform_fee', 15, 2)->default(0)->after('total_amount');
            $table->decimal('fixed_fee', 15, 2)->default(0)->after('platform_fee');
            $table->decimal('service_fee', 15, 2)->default(0)->after('fixed_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->dropColumn(['platform_fee', 'fixed_fee', 'service_fee']);
        });
    }
};
