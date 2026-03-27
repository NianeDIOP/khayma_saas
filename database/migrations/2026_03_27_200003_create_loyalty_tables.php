<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->integer('points_per_amount')->default(1);        // 1 point per X
            $table->decimal('amount_per_point', 12, 2)->default(1000); // 1000 XOF
            $table->integer('redemption_threshold')->default(100);    // min points to redeem
            $table->decimal('redemption_value', 12, 2)->default(2000); // value in XOF per threshold
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('company_id');
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // earn, redeem
            $table->integer('points');
            $table->decimal('monetary_value', 12, 2)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'customer_id']);
            $table->index(['customer_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_configs');
    }
};
