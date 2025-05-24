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
        Schema::create('production_tailor_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tailor_id')->constrained('tailors')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_amount', 18, 2);

            $table->enum('status', ['pending', 'paid'])->default('pending');

            $table->datetime('created_datetime')->nullable();
            $table->datetime('updated_datetime')->nullable();

            $table->foreignId('created_by_uid')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_uid')->nullable()->constrained('users')->onDelete('set null');

            $table->text('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_tailor_payrolls');
    }
};
