<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // ex: "Rouge - XL"
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('price_override', 12, 2)->nullable(); // null = use product price
            $table->decimal('purchase_price_override', 12, 2)->nullable();
            $table->jsonb('attributes')->nullable(); // {"couleur":"Rouge","taille":"XL"}
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'sku']);
            $table->unique(['company_id', 'barcode']);
            $table->index(['product_id', 'is_active']);
        });

        // Stock tracking per variant + depot
        Schema::create('variant_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depot_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['product_variant_id', 'depot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_stock_items');
        Schema::dropIfExists('product_variants');
    }
};
