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
        Schema::create('products', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('shop_id')->nullable();
            $table->string('category_name')->nullable();
            $table->string('name');
            $table->string('sku')->index();
            $table->string('unit')->nullable();

            $table->timestamps();
            $table->unique(['shop_id', 'sku']);

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
