<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paydunya_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('billing_period', 20)->default('monthly'); // monthly|quarterly|yearly
            $table->unsignedBigInteger('amount');                      // in XOF
            $table->string('status', 20)->default('pending');          // pending|success|failed|cancelled
            $table->string('paydunya_token', 150)->nullable()->unique();
            $table->string('invoice_url', 600)->nullable();
            $table->string('payment_reference', 150)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paydunya_transactions');
    }
};
