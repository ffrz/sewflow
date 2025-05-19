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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('model');
            $table->string('size');
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->enum('material_source', ['brand', 'konveksi']);
            $table->enum('status', ['pending', 'producing', 'completed', 'returned'])->default('pending');
            $table->text('notes')->nullable();

            $table->datetime('created_datetime')->nullable();
            $table->datetime('updated_datetime')->nullable();
            $table->unsignedBigInteger('created_by_uid')->nullable();
            $table->unsignedBigInteger('updated_by_uid')->nullable();

            $table->foreign('created_by_uid')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_uid')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
