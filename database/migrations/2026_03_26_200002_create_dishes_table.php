<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('available_morning')->default(true);
            $table->boolean('available_noon')->default(true);
            $table->boolean('available_evening')->default(true);
            $table->boolean('is_additional')->default(false); // boissons, extras
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->date('promo_start')->nullable();
            $table->date('promo_end')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'restaurant_category_id']);
            $table->index(['company_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
