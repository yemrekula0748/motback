<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Realm extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'host',
        'port',
        'map_path',
        'faction',
        'is_active',
        'is_default',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'weight' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function toApiArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'host' => $this->host,
            'port' => $this->port,
            'map_path' => $this->map_path,
            'faction' => $this->faction,
            'is_active' => (bool) $this->is_active,
            'is_default' => (bool) $this->is_default,
            'weight' => $this->weight,
        ];
    }
}
