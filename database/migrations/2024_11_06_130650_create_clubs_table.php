<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('description', 1080);
            $table->string('image');
            $table->integer('nombre de membres actuel')->default(1);;
            $table->integer('nombre de membres max')->default(50);
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->on('users')->references('id');
            $table->foreignId('status_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
