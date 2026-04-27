<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 30)->unique();
            $table->enum('class', ['savasco', 'okcu', 'saman']);
            $table->integer('level')->default(1);
            $table->bigInteger('experience')->default(0);
            $table->integer('strength')->default(10);
            $table->integer('agility')->default(10);
            $table->integer('intelligence')->default(10);
            $table->integer('endurance')->default(10);
            $table->integer('max_health')->default(100);
            $table->integer('current_health')->default(100);
            $table->integer('max_mana')->default(100);
            $table->integer('current_mana')->default(100);
            $table->string('current_map', 100)->default('ThirdPersonMap');
            $table->float('pos_x')->default(0);
            $table->float('pos_y')->default(0);
            $table->float('pos_z')->default(0);
            $table->integer('gold')->default(100);
            $table->integer('silver')->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
