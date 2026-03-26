<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('ninea', 50)->nullable();
            $table->string('rib')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->decimal('outstanding_balance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->unique(['company_id', 'ninea'], 'suppliers_company_ninea_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
