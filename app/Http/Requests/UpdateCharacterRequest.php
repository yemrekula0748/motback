<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced by the auth:sanctum middleware on the route.
        return true;
    }

    public function rules(): array
    {
        return [
            // Progression
            'level'               => 'sometimes|integer|min:1',
            'experience'          => 'sometimes|integer|min:0',

            // Current (total) stats
            'strength'            => 'sometimes|integer|min:0',
            'agility'             => 'sometimes|integer|min:0',
            'intelligence'        => 'sometimes|integer|min:0',
            'endurance'           => 'sometimes|integer|min:0',

            // Base (class/origin) stats
            'base_strength'       => 'sometimes|integer|min:0',
            'base_agility'        => 'sometimes|integer|min:0',
            'base_intelligence'   => 'sometimes|integer|min:0',
            'base_endurance'      => 'sometimes|integer|min:0',

            // Alias stats
            'vitality'            => 'sometimes|integer|min:0',
            'dexterity'           => 'sometimes|integer|min:0',

            // Unspent points
            'unspent_stat_points' => 'sometimes|integer|min:0',

            // Health / mana
            'max_health'          => 'sometimes|integer|min:0',
            'current_health'      => 'sometimes|integer|min:0',
            'max_mana'            => 'sometimes|integer|min:0',
            'current_mana'        => 'sometimes|integer|min:0',

            // Combat
            'attack_power'        => 'sometimes|integer|min:0',
            'defense'             => 'sometimes|integer|min:0',

            // Economy
            'gold'                => 'sometimes|integer|min:0',
            'silver'              => 'sometimes|integer|min:0',

            // World position
            'current_map'         => 'sometimes|string|max:100',
            'pos_x'               => 'sometimes|numeric',
            'pos_y'               => 'sometimes|numeric',
            'pos_z'               => 'sometimes|numeric',
        ];
    }
}
