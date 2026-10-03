<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('air_conditioner_variants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('air_conditioner_id')->constrained()->onDelete('cascade');
        $table->string('capacity_name'); // VD: 9.000 BTU (1 HP)
        $table->decimal('price', 15, 2)->nullable();
        $table->integer('stock')->default(10);
        $table->string('image')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('air_conditioner_variants');
    }
};