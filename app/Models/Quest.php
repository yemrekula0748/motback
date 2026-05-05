<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = [
        'title',
        'description',
        'target_enemy',
        'required_kills',
        'min_level',
        'reward_exp',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function characterQuests()
    {
        return $this->hasMany(CharacterQuest::class);
    }
}
