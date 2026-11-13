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
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('freight_amount', 14, 2)->nullable()->after('volume_cbm');
            $table->char('currency_code', 3)->nullable()->after('freight_amount');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->char('currency_code', 3)->default('IDR')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['freight_amount', 'currency_code']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('currency_code');
        });
    }
};
