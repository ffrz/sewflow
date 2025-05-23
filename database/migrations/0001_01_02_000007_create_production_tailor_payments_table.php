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
        Schema::create('production_tailor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_assignment_id')->constrained('production_work_assignments')->onDelete('cascade');
            $table->foreignId('payroll_id')->constrained('production_tailor_payrolls')->onDelete('cascade');

            $table->decimal('amount', 15, 2);
            $table->dateTime('payment_date');
            $table->enum('payment_method', ['cash', 'transfer', 'other'])->default('cash');
            $table->text('notes')->nullable();

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
        Schema::dropIfExists('production_tailor_payments');
    }
};
