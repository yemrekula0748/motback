<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterQuest extends Model
{
    protected $fillable = [
        'character_id',
        'quest_id',
        'kill_count',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }
}
