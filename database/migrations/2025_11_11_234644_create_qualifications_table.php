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
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->boolean('is_trusted_vip')->default(false);
            $table->boolean('deposit')->default(false);
            $table->boolean('is_verified_by_admin')->default(false);
            $table->enum('age', ['A', 'B', 'C', 'D'])->nullable();
            $table->enum('salary', ['A', 'B', 'C', 'D'])->nullable();
            $table->enum('location', ['1', '2', '3', '4', '5'])->nullable();
            $table->enum('rating', ['1', '2', '3', '4', '5'])->nullable();
            $table->string('code', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};
