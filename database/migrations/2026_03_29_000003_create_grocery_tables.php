<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grocery_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });

        Schema::create('grocery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grocery_list_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('completed')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grocery_items');
        Schema::dropIfExists('grocery_lists');
    }
};
