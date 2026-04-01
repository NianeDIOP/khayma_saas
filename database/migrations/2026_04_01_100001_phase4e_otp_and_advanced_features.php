<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 4E.1 : OTP Authentication ─────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->unique()->after('email');
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->index();
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        // ── 4E.2 : Cloudflare R2 / Storage tracking ──────────────
        Schema::create('company_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('disk', 20)->default('public');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->string('folder', 100)->default('general'); // logical folder
            $table->timestamps();

            $table->index(['company_id', 'folder']);
        });

        // ── 4E.3 : Loyalty Tiers ─────────────────────────────────
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');                        // Bronze, Silver, Gold
            $table->unsignedInteger('min_points');         // seuil d'entrée
            $table->decimal('bonus_multiplier', 4, 2)->default(1.00); // ×1.5, ×2 ...
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'min_points']);
        });

        // Add tier tracking on customers
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('loyalty_tier_id')->nullable()->after('loyalty_points')->constrained('loyalty_tiers')->nullOnDelete();
        });

        // ── 4E.4 : Depot Transfer Approval ───────────────────────
        Schema::table('depot_transfers', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('depot_transfers', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'rejection_reason']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['loyalty_tier_id']);
            $table->dropColumn('loyalty_tier_id');
        });

        Schema::dropIfExists('loyalty_tiers');
        Schema::dropIfExists('company_files');
        Schema::dropIfExists('otp_codes');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
