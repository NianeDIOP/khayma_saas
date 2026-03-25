<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('code', 30)->unique();               // starter | pro | premium
            $table->unsignedInteger('max_products')->default(100);
            $table->unsignedInteger('max_users')->default(3);
            $table->unsignedInteger('max_storage_gb')->default(2);
            $table->unsignedInteger('max_transactions_month')->default(500);
            $table->unsignedInteger('api_rate_limit')->default(100);
            $table->unsignedBigInteger('price_monthly')->default(0);   // en XOF
            $table->unsignedBigInteger('price_quarterly')->default(0);
            $table->unsignedBigInteger('price_yearly')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('code', 30)->unique();               // restaurant | quincaillerie | boutique | location
            $table->text('description')->nullable();
            $table->string('icon', 60)->nullable();             // classe Font Awesome
            $table->unsignedBigInteger('installation_fee')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['company_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_modules');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('plans');
    }
};
