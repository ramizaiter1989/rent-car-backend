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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('car_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('amount');
            $table->date('issue_date');
            $table->decimal('commission', 5, 2);
            $table->decimal('app_fees', 10, 2);
            $table->integer('net_profit');
            $table->string('promo_code', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
