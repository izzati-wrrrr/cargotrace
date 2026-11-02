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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number', 40)->unique();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('carrier_id')->constrained();
            $table->foreignId('origin_location_id')->constrained('locations');
            $table->foreignId('destination_location_id')->constrained('locations');
            $table->foreignId('created_by')->constrained('users');
            $table->string('cargo_type', 100);
            $table->decimal('weight_kg', 12, 2);
            $table->decimal('volume_cbm', 12, 2)->nullable();
            $table->dateTime('expected_departure_at');
            $table->dateTime('expected_arrival_at');
            $table->dateTime('estimated_arrival_at')->nullable();
            $table->string('current_status', 20)->default('CREATED');
            $table->string('delay_severity', 10)->default('NONE');
            $table->timestamps();
            $table->softDeletes();

            $table->index('current_status');
            $table->index('expected_arrival_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
