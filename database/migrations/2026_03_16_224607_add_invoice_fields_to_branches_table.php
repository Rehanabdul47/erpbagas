<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('invoice_name')->nullable()->after('phone');
            $table->string('invoice_tagline')->nullable()->after('invoice_name');
            $table->string('invoice_footer')->nullable()->after('invoice_tagline');
            $table->text('invoice_notes')->nullable()->after('invoice_footer');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['invoice_name', 'invoice_tagline', 'invoice_footer', 'invoice_notes']);
        });
    }
};
