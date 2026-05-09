<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    /**
     * Transform the character model into a consistent API shape.
     *
     * Backward-compatible fallbacks applied here:
     *  - vitality  : model accessor returns endurance when null
     *  - dexterity : model accessor returns agility when null
     *  - base_*    : returns current stat when stored value is 0 (pre-migration rows)
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'name'                => $this->name,
            'class'               => $this->class,
            'level'               => $this->level,
            'experience'          => $this->experience,

            // Current (total) stats - what the player has right now
            'strength'            => $this->strength,
            'agility'             => $this->agility,
            'intelligence'        => $this->intelligence,
            'endurance'           => $this->endurance,

            // Base (class/origin) stats - fall back to current if not yet initialised (= 0)
            'base_strength'       => $this->base_strength ?: $this->strength,
            'base_agility'        => $this->base_agility  ?: $this->agility,
            'base_intelligence'   => $this->base_intelligence ?: $this->intelligence,
            'base_endurance'      => $this->base_endurance    ?: $this->endurance,

            // Alias stats (model accessors handle null -> fallback)
            'vitality'            => $this->vitality,   // endurance fallback via accessor
            'dexterity'           => $this->dexterity,  // agility fallback via accessor

            'unspent_stat_points' => $this->unspent_stat_points,

            // Health / mana
            'max_health'          => $this->max_health,
            'current_health'      => $this->current_health,
            'max_mana'            => $this->max_mana,
            'current_mana'        => $this->current_mana,

            // Combat (persisted as sent by Unreal)
            'attack_power'        => $this->attack_power,
            'defense'             => $this->defense,

            // Economy
            'gold'                => $this->gold,
            'silver'              => $this->silver,

            // World position
            'current_map'         => $this->current_map,
            'pos_x'               => $this->pos_x,
            'pos_y'               => $this->pos_y,
            'pos_z'               => $this->pos_z,
        ];
    }
}
