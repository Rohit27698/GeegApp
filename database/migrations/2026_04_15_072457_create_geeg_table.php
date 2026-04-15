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
        Schema::create('geeg', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('discription');
            $table->string('subject');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('open');
            $table->timestamp('deadline')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('assign_to')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geeg');
    }
};
