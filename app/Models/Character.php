<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'base_strength',
        'base_agility',
        'base_intelligence',
        'base_endurance',
        'unspent_stat_points',
        'max_health',
        'current_health',
        'max_mana',
        'current_mana',
        'gold',
        'attack_power',
        'defense',
        'current_map',
        'pos_x',
        'pos_y',
        'pos_z',
    ];

    protected function casts(): array
    {
        return [
            'pos_x' => 'float',
            'pos_y' => 'float',
            'pos_z' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'class' => $this->class,
            'level' => $this->level,
            'experience' => $this->experience,
            'strength' => $this->strength,
            'agility' => $this->agility,
            'intelligence' => $this->intelligence,
            'endurance' => $this->endurance,
            'base_strength' => $this->base_strength,
            'base_agility' => $this->base_agility,
            'base_intelligence' => $this->base_intelligence,
            'base_endurance' => $this->base_endurance,
            'unspent_stat_points' => $this->unspent_stat_points,
            'max_health' => $this->max_health,
            'current_health' => $this->current_health,
            'max_mana' => $this->max_mana,
            'current_mana' => $this->current_mana,
            'gold' => $this->gold,
            'attack_power' => $this->attack_power,
            'defense' => $this->defense,
            'current_map' => $this->current_map,
            'pos_x' => $this->pos_x,
            'pos_y' => $this->pos_y,
            'pos_z' => $this->pos_z,
        ];
    }
}
