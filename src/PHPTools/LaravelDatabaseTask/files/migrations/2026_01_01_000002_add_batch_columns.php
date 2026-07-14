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
        if (! Schema::hasColumns('database_task_inputs', ['batch_order'])) {
            Schema::table('database_task_inputs', function (Blueprint $table) {
                $table->unsignedInteger('batch_order')->default(0)->after('is_excluded')->index();
            });
        }

        if (! Schema::hasColumns('database_task_outputs', ['batch_order'])) {
            Schema::table('database_task_outputs', function (Blueprint $table) {
                $table->unsignedInteger('batch_order')->default(0)->after('is_file')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
