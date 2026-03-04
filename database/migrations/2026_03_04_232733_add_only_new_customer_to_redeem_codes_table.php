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
        Schema::table('redeem_codes', function (Blueprint $table) {
            $table->boolean('only_new_customer')->default(false)->after('max_uses_per_customer');
            $table->boolean('only_returning_customer')->default(false)->after('only_new_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redeem_codes', function (Blueprint $table) {
            $table->dropColumn(['only_new_customer', 'only_returning_customer']);
        });
    }
};
