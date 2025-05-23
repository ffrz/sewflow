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
        Schema::create('production_work_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_assignment_id')->constrained('production_work_assignments')->onDelete('cascade');
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'returned']);
            $table->text('notes')->nullable();
            $table->dateTime('logged_at');

            $table->datetime('created_datetime')->nullable();
            $table->datetime('updated_datetime')->nullable();

            $table->foreignId('created_by_uid')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_uid')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_work_progress_logs');
    }
};
