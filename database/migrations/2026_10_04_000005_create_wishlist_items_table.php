<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('air_conditioner_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_snapshot', 14, 2)->nullable();
            $table->unsignedInteger('stock_snapshot')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'air_conditioner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
