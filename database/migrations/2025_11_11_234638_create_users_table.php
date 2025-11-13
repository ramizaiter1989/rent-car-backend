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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('phone_number', 20)->unique(); // UNIQUE for login
            $table->string('password');
            $table->boolean('verified_by_admin')->default(false);
            $table->boolean('otp_verification')->default(false);
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->text('id_card_front')->nullable();
            $table->text('id_card_back')->nullable();
            $table->enum('city', ['beirut', 'tripoli', 'sidon', 'tyre', 'other'])->nullable();
            $table->text('bio')->nullable();
            $table->enum('role', ['agency', 'client', 'employee', 'admin', 'company', 'advertiser', 'third_party']);
            $table->text('profile_picture')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->text('referred_link')->nullable();
            $table->foreignId('referral_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('community_ids')->nullable();
            $table->boolean('update_access')->default(true);
            $table->string('qualification_code', 50)->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('phone_number'); // Important for login queries
            $table->index(['role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};