<?php

use App\Models\Order;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->dateTime('date');
            $table->dateTime('due_date')->nullable();
            $table->enum('type', array_keys(Order::Types))->default(Order::Type_Maklon);
            $table->enum('status', array_keys(Order::Statuses))->default(Order::Status_Draft);
            $table->enum('payment_status', array_keys(Order::PaymentStatuses))->default(Order::PaymentStatus_Unpaid);
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
        Schema::dropIfExists('orders');
    }
};
