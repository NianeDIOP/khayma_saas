<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_progress', function (Blueprint $table) {
            $table->id();
            $table->string('phase')->unique();        // ex: phase0, phase1a
            $table->jsonb('step_states')->default('{}'); // { "tasks-01": [true,false,...], ... }
            $table->integer('done')->default(0);
            $table->integer('total')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_progress');
    }
};
