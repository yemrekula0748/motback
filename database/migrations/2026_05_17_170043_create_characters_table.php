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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 24)->unique();
            $table->string('class', 32)->index();
            $table->unsignedInteger('level')->default(1);
            $table->unsignedBigInteger('experience')->default(0);
            $table->unsignedInteger('strength')->default(10);
            $table->unsignedInteger('agility')->default(10);
            $table->unsignedInteger('intelligence')->default(10);
            $table->unsignedInteger('endurance')->default(10);
            $table->unsignedInteger('base_strength')->default(10);
            $table->unsignedInteger('base_agility')->default(10);
            $table->unsignedInteger('base_intelligence')->default(10);
            $table->unsignedInteger('base_endurance')->default(10);
            $table->unsignedInteger('unspent_stat_points')->default(0);
            $table->unsignedInteger('max_health')->default(300);
            $table->unsignedInteger('current_health')->default(300);
            $table->unsignedInteger('max_mana')->default(100);
            $table->unsignedInteger('current_mana')->default(100);
            $table->unsignedBigInteger('gold')->default(0);
            $table->unsignedInteger('attack_power')->default(25);
            $table->unsignedInteger('defense')->default(10);
            $table->string('current_map')->default('/Game/Maps/Gokboru');
            $table->decimal('pos_x', 12, 2)->default(0);
            $table->decimal('pos_y', 12, 2)->default(0);
            $table->decimal('pos_z', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
