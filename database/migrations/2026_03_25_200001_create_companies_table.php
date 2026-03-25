<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('sector', 60)->nullable();           // restaurant, quincaillerie…
            $table->string('ninea', 30)->nullable();            // identifiant fiscal sénégalais
            $table->char('currency', 3)->default('XOF');
            $table->string('timezone', 60)->default('Africa/Dakar');
            $table->string('primary_color', 10)->default('#10B981');
            $table->string('secondary_color', 10)->default('#F59E0B');
            $table->string('subscription_status', 20)->default('trial'); // trial|active|suspended|cancelled
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('subscription_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
