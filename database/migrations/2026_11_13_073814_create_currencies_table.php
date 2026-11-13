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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->char('code', 3)->unique();
            $table->decimal('rate', 24, 8); // jumlah mata uang ini untuk 1 USD
            $table->timestamp('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
