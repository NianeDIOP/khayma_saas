<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->enum('type', ['table', 'takeaway', 'delivery']);
            $table->string('table_number')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('delivery_person')->nullable();
            $table->string('customer_name')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('cancel_reason')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'wave', 'om', 'card', 'other'])->default('cash');
            $table->enum('payment_status', ['paid', 'pending'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
