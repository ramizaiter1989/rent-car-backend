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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->integer('amount');
            $table->string('reference_id', 100)->unique();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('issue_date');
            $table->enum('source', ['whish', 'omt', 'bank', 'cash']);
            $table->enum('type', ['income', 'expense', 'debit', 'upcoming_income']);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
