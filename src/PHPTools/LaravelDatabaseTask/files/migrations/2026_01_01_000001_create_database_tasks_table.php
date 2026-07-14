<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPTools\LaravelDatabaseTask\Enums;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('database_tasks', function (Blueprint $table) {
            $table->id();
            $table->morphs('user');
            $table->string('task_class')->index();
            $table->string('title');
            $table->text('description');
            $table->string('risk')->default(Enums\TaskRisk::MEDIUM->value);
            $table->string('status')->default(Enums\TaskStatus::UNAPPLIED->value);
            $table->timestamp('schedules_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('database_task_inputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('database_task_id')->index();
            $table->string('input_class')->index();
            $table->text('input_value');
            $table->boolean('is_file')->default(false);
            $table->boolean('is_excluded')->default(false);
            $table->timestamps();
        });

        Schema::create('database_task_outputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('database_task_id')->index();
            $table->string('output_class')->index();
            $table->text('output_value');
            $table->boolean('is_file')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_task_outputs');

        Schema::dropIfExists('database_task_inputs');

        Schema::dropIfExists('database_tasks');
    }
};
