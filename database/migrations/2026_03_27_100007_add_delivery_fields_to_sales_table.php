<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('delivery_address')->nullable()->after('notes');
            $table->decimal('delivery_fee', 12, 2)->default(0)->after('delivery_address');
            $table->string('delivery_status', 20)->nullable()->after('delivery_fee'); // null, preparing, delivered, confirmed
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['delivery_address', 'delivery_fee', 'delivery_status']);
        });
    }
};
