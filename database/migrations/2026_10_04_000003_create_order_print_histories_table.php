<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_print_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('print_type', 30)->default('single');
            $table->timestamp('printed_at');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'printed_at']);
            $table->index(['print_type', 'printed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_print_histories');
    }
};
