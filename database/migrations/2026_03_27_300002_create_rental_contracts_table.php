<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->boolean('deposit_returned')->default(false);
            $table->decimal('deposit_returned_amount', 12, 2)->default(0);
            $table->enum('payment_frequency', ['daily', 'monthly', 'quarterly', 'yearly', 'one_time'])->default('monthly');
            $table->enum('status', ['active', 'completed', 'overdue', 'cancelled', 'renewed'])->default('active');
            $table->text('conditions')->nullable();
            $table->jsonb('inspection_start')->nullable();  // description + photos départ
            $table->jsonb('inspection_end')->nullable();    // description + photos retour
            $table->text('notes')->nullable();
            $table->foreignId('renewed_from_id')->nullable()->constrained('rental_contracts')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'rental_asset_id']);
            $table->index(['company_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_contracts');
    }
};
