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
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_source_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('previous_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('PLN');
            $table->boolean('is_available')->default(true);
            $table->json('raw_data')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('scraped_at');
            $table->timestamps();

            $table->index(['product_source_id', 'scraped_at']);
            $table->index(['scraped_at', 'is_available']);
            $table->index(['price', 'currency']);

            $table->index(['product_source_id', 'price', 'scraped_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
