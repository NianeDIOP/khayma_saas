<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('nif', 50)->nullable();
            $table->enum('category', ['normal', 'vip', 'professional'])->default('normal');
            $table->integer('loyalty_points')->default(0);
            $table->decimal('outstanding_balance', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->unique(['company_id', 'nif'], 'customers_company_nif_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
