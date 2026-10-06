<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

            DB::table('database_tasks')->where('status', 'unapplied')->update(['status' => 'validated']);
            DB::table('database_tasks')->where('status', 'pending')->update(['status' => 'requested']);
            DB::table('database_tasks')->where('status', 'approved')->update(['status' => 'ready']);
            DB::table('database_tasks')->where('status', 'rejected')->update(['status' => 'failed']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
