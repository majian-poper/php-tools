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
        if (! Schema::hasColumns('database_task_inputs', ['validated_at'])) {
            Schema::table('database_task_inputs', function (Blueprint $table) {
                $table->timestamp('validated_at')->nullable()->after('batch_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
