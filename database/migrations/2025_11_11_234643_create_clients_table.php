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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('license_number', 100)->nullable();
            $table->text('driver_license')->nullable();
            $table->enum('profession', ['employee', 'freelancer', 'business', 'student', 'other'])->nullable();
            $table->enum('avg_salary', ['200-500', '500-1000', '1000-2000', '2000+'])->nullable();
            $table->string('promo_code', 50)->nullable();
            $table->json('rating')->nullable();
            $table->integer('deposit')->default(0);
            $table->integer('bonus')->default(0);
            $table->boolean('trusted_by_app')->default(false);
            $table->string('qualification_code', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
