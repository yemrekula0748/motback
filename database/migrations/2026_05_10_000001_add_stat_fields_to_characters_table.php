<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            // Base stats (class/origin source) - default 0 means not yet initialized (fallback to current stat)
            $table->integer('base_strength')->default(0)->after('endurance');
            $table->integer('base_agility')->default(0)->after('base_strength');
            $table->integer('base_intelligence')->default(0)->after('base_agility');
            $table->integer('base_endurance')->default(0)->after('base_intelligence');

            // Unspent stat points granted by Unreal server on level-up
            $table->integer('unspent_stat_points')->default(0)->after('base_endurance');

            // Alias stats: nullable so we can detect "not sent by Unreal" and fall back
            $table->integer('vitality')->nullable()->after('unspent_stat_points');  // fallback: endurance
            $table->integer('dexterity')->nullable()->after('vitality');            // fallback: agility

            // Combat derived stats (stored as sent by Unreal; computed on Unreal side)
            $table->integer('attack_power')->default(0)->after('dexterity');
            $table->integer('defense')->default(0)->after('attack_power');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'base_strength',
                'base_agility',
                'base_intelligence',
                'base_endurance',
                'unspent_stat_points',
                'vitality',
                'dexterity',
                'attack_power',
                'defense',
            ]);
        });
    }
};
