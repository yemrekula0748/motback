<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'class',
        'level',
        'experience',
        // Current (total) stats
        'strength',
        'agility',
        'intelligence',
        'endurance',
        // Base (class/origin) stats
        'base_strength',
        'base_agility',
        'base_intelligence',
        'base_endurance',
        // Alias stats (fallback to endurance/agility if null)
        'vitality',
        'dexterity',
        // Unspent points from Unreal server level-ups
        'unspent_stat_points',
        // Combat stats (computed on Unreal side, persisted here)
        'attack_power',
        'defense',
        // Health / mana
        'max_health',
        'current_health',
        'max_mana',
        'current_mana',
        // Economy & position
        'gold',
        'silver',
        'current_map',
        'pos_x',
        'pos_y',
        'pos_z',
        'last_played_at',
    ];

    protected $casts = [
        'level'               => 'integer',
        'experience'          => 'integer',
        'strength'            => 'integer',
        'agility'             => 'integer',
        'intelligence'        => 'integer',
        'endurance'           => 'integer',
        'base_strength'       => 'integer',
        'base_agility'        => 'integer',
        'base_intelligence'   => 'integer',
        'base_endurance'      => 'integer',
        'unspent_stat_points' => 'integer',
        'attack_power'        => 'integer',
        'defense'             => 'integer',
        'max_health'          => 'integer',
        'current_health'      => 'integer',
        'max_mana'            => 'integer',
        'current_mana'        => 'integer',
        'gold'                => 'integer',
        'silver'              => 'integer',
        'pos_x'               => 'float',
        'pos_y'               => 'float',
        'pos_z'               => 'float',
        'last_played_at'      => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Accessors: alias stats with backward-compatible fallbacks
    // -------------------------------------------------------------------------

    /**
     * vitality: stored when Unreal sends it explicitly.
     * Falls back to endurance when null (legacy or not sent).
     */
    protected function vitality(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? ($this->attributes['endurance'] ?? null),
            set: fn ($value) => ['vitality' => $value],
        );
    }

    /**
     * dexterity: stored when Unreal sends it explicitly.
     * Falls back to agility when null (legacy or not sent).
     */
    protected function dexterity(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? ($this->attributes['agility'] ?? null),
            set: fn ($value) => ['dexterity' => $value],
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quests()
    {
        return $this->hasMany(CharacterQuest::class);
    }

    /**
     * Returns starting stats for a given class.
     * base_* mirrors current stats at creation time (they diverge as the player spends points).
     * unspent_stat_points starts at 0 (Unreal grants points on level-up).
     */
    public static function getStartingStats(string $class): array
    {
        $base = match ($class) {
            'savasco' => [
                'strength'      => 15,
                'agility'       => 10,
                'intelligence'  => 5,
                'endurance'     => 15,
                'max_health'    => 150,
                'current_health'=> 150,
                'max_mana'      => 50,
                'current_mana'  => 50,
            ],
            'okcu' => [
                'strength'      => 10,
                'agility'       => 18,
                'intelligence'  => 8,
                'endurance'     => 10,
                'max_health'    => 110,
                'current_health'=> 110,
                'max_mana'      => 80,
                'current_mana'  => 80,
            ],
            'saman' => [
                'strength'      => 5,
                'agility'       => 8,
                'intelligence'  => 20,
                'endurance'     => 8,
                'max_health'    => 80,
                'current_health'=> 80,
                'max_mana'      => 150,
                'current_mana'  => 150,
            ],
            default => [
                'strength'      => 10,
                'agility'       => 10,
                'intelligence'  => 10,
                'endurance'     => 10,
                'max_health'    => 100,
                'current_health'=> 100,
                'max_mana'      => 100,
                'current_mana'  => 100,
            ],
        };

        // base_* mirrors current stats at character creation; diverge as player spends points
        return array_merge($base, [
            'base_strength'       => $base['strength'],
            'base_agility'        => $base['agility'],
            'base_intelligence'   => $base['intelligence'],
            'base_endurance'      => $base['endurance'],
            'unspent_stat_points' => 0,
            'attack_power'        => 0,
            'defense'             => 0,
        ]);
    }

    public function applyLevelUp(): void
    {
        $this->level++;
        $this->strength += match ($this->class) {
            'savasco' => 3,
            'okcu' => 1,
            'saman' => 1,
            default => 2,
        };
        $this->agility += match ($this->class) {
            'savasco' => 1,
            'okcu' => 3,
            'saman' => 1,
            default => 2,
        };
        $this->intelligence += match ($this->class) {
            'savasco' => 1,
            'okcu' => 1,
            'saman' => 3,
            default => 2,
        };
        $this->endurance += match ($this->class) {
            'savasco' => 2,
            'okcu' => 1,
            'saman' => 1,
            default => 2,
        };
        $this->max_health = 100 + ($this->endurance * 10);
        $this->max_mana = 50 + ($this->intelligence * 10);
    }
}
