<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add variant_id to sale_items so POS can track which variant was sold
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });

        // Add variant_id to stock_movements for variant-level tracking
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });

        // Add depot_id to sales so POS can choose which depot to sell from
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('depot_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->decimal('loyalty_points_earned', 10, 0)->default(0)->after('notes');
            $table->decimal('loyalty_points_used', 10, 0)->default(0)->after('loyalty_points_earned');
            $table->decimal('loyalty_discount', 12, 2)->default(0)->after('loyalty_points_used');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('depot_id');
            $table->dropColumn(['loyalty_points_earned', 'loyalty_points_used', 'loyalty_discount']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
