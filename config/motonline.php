<?php

return [
    'allowed_factions' => ['gokboru', 'bozkurt'],
    'allowed_classes' => ['savasco', 'okcu', 'saman'],
    'max_characters_per_user' => env('MOTONLINE_MAX_CHARACTERS_PER_USER', 3),
    'game_session_ttl_seconds' => env('MOTONLINE_GAME_SESSION_TTL', 30),
    'server_shared_key' => env('MOTONLINE_SERVER_SHARED_KEY', ''),
    'default_map_path' => env('MOTONLINE_DEFAULT_MAP_PATH', '/Game/Maps/Gokboru'),
];
