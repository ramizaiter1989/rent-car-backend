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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('website')->nullable();
            $table->text('company_type')->nullable();
            $table->text('image_url')->nullable();
            $table->text('target_url')->nullable();
            $table->text('ads_text')->nullable();
            $table->integer('amount_cost');
            $table->dateTime('start_at');
            $table->dateTime('expire_at');
            $table->integer('nb_views')->default(0);
            $table->integer('nb_clicks')->default(0);
            $table->boolean('online')->default(false);
            $table->timestamps();
            
            $table->index(['online', 'expire_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
