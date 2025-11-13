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
        Schema::create('check_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->text('photo_front')->nullable();
            $table->text('photo_back')->nullable();
            $table->text('photo_side_left')->nullable();
            $table->text('photo_side_right')->nullable();
            $table->text('photo_tableau')->nullable();
            $table->integer('mileage_number')->nullable();
            $table->enum('fuel_load', ['10%', '25%', '50%', '75%', '100%'])->nullable();
            $table->text('photo_inside_front')->nullable();
            $table->text('photo_inside_back')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_photos');
    }
};
