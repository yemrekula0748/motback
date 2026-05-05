<?php

namespace App\Models;

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
        'strength',
        'agility',
        'intelligence',
        'endurance',
        'max_health',
        'current_health',
        'max_mana',
        'current_mana',
        'current_map',
        'pos_x',
        'pos_y',
        'pos_z',
        'gold',
        'silver',
        'last_played_at',
    ];

    protected $casts = [
        'last_played_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quests()
    {
        return $this->hasMany(CharacterQuest::class);
    }

    public static function getStartingStats(string $class): array
    {
        return match ($class) {
            'savasco' => [
                'strength' => 15,
                'agility' => 10,
                'intelligence' => 5,
                'endurance' => 15,
                'max_health' => 150,
                'current_health' => 150,
                'max_mana' => 50,
                'current_mana' => 50,
            ],
            'okcu' => [
                'strength' => 10,
                'agility' => 18,
                'intelligence' => 8,
                'endurance' => 10,
                'max_health' => 110,
                'current_health' => 110,
                'max_mana' => 80,
                'current_mana' => 80,
            ],
            'saman' => [
                'strength' => 5,
                'agility' => 8,
                'intelligence' => 20,
                'endurance' => 8,
                'max_health' => 80,
                'current_health' => 80,
                'max_mana' => 150,
                'current_mana' => 150,
            ],
            default => [
                'strength' => 10,
                'agility' => 10,
                'intelligence' => 10,
                'endurance' => 10,
                'max_health' => 100,
                'current_health' => 100,
                'max_mana' => 100,
                'current_mana' => 100,
            ],
        };
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
