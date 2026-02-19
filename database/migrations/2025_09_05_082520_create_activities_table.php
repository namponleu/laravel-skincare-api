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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // login, logout, user_created, order_created, etc.
            $table->string('description');
            $table->string('user_type')->nullable(); // admin, user
            $table->string('username')->nullable(); // who performed the action
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable(); // additional data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
