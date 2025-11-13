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
        Schema::create('frequent_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->integer('year')->nullable();
            $table->enum('cylinder_number', ['4', '6', '8'])->nullable();
            $table->string('color', 100)->nullable();
            $table->integer('mileage')->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid'])->nullable();
            $table->enum('transmission', ['automatic', 'manual'])->nullable();
            $table->enum('wheels_drive', ['4x4', '2_front', '2_back', 'autoblock'])->nullable();
            $table->enum('car_category', ['luxury', 'sport', 'commercial', 'industrial', 'normal', 'event', 'sea'])->nullable();
            $table->text('car_add_on')->nullable();
            $table->integer('seats')->nullable();
            $table->integer('doors')->nullable();
            $table->json('features')->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->decimal('holiday_rate', 10, 2)->nullable();
            $table->boolean('is_deposit')->default(false);
            $table->decimal('deposit', 10, 2)->nullable();
            $table->json('delivery_location')->nullable();
            $table->json('return_location')->nullable();
            $table->boolean('is_delivered')->default(false);
            $table->decimal('delivery_fees', 10, 2)->nullable();
            $table->boolean('with_driver')->default(false);
            $table->decimal('driver_fees', 10, 2)->nullable();
            $table->integer('max_driving_mileage')->nullable();
            $table->integer('min_renting_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frequent_searches');
    }
};
