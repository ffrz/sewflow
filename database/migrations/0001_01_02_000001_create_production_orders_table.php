<?php

use App\Models\ProductionOrder;
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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->dateTime('date');
            $table->dateTime('due_date')->nullable();
            $table->string('model', 100);
            $table->string('material_info', 100)->nullable(); // apakah butuh??
            $table->enum('type', array_keys(ProductionOrder::Types))->default(ProductionOrder::Type_Maklon);
            $table->enum('status', array_keys(ProductionOrder::Statuses))->default(ProductionOrder::Status_Draft);
            $table->enum('payment_status', array_keys(ProductionOrder::PaymentStatuses))->default(ProductionOrder::PaymentStatus_Unpaid);
            $table->enum('delivery_status', array_keys(ProductionOrder::DeliveryStatuses))->default(ProductionOrder::DeliveryStatus_NotSent);
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
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
        Schema::dropIfExists('production_orders');
    }
};
