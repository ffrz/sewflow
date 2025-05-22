<?php

use App\Models\ProductionWorkAssignment;
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
        Schema::create('production_work_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('production_order_items')->onDelete('cascade');
            $table->foreignId('tailor_id')->constrained('tailors')->onDelete('restrict');
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('returned_quantity')->default(0);
            $table->dateTime('datetime');
            $table->enum('status', array_keys(ProductionWorkAssignment::Statuses))->default(ProductionWorkAssignment::Status_Assigned);
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
        Schema::dropIfExists('production_work_assignments');
    }
};
