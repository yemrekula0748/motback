<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->shouldCreateGameSessionsTable()) {
            return;
        }

        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('realm_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('client_ip', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }

    private function shouldCreateGameSessionsTable(): bool
    {
        if (! Schema::hasTable('game_sessions')) {
            return true;
        }

        if ($this->hasExpectedStructure()) {
            return false;
        }

        if (DB::table('game_sessions')->count() > 0) {
            throw new \RuntimeException('The pending game_sessions migration found a partially created table with data. Please back up the data and resolve the migration state manually.');
        }

        Schema::drop('game_sessions');

        return true;
    }

    private function hasExpectedStructure(): bool
    {
        return $this->foreignKeyExists('game_sessions_user_id_foreign')
            && $this->foreignKeyExists('game_sessions_character_id_foreign')
            && $this->foreignKeyExists('game_sessions_realm_id_foreign')
            && $this->indexExists('game_sessions_token_hash_unique')
            && $this->indexExists('game_sessions_expires_at_index')
            && $this->indexExists('game_sessions_consumed_at_index');
    }

    private function foreignKeyExists(string $constraintName): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'game_sessions')
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'game_sessions')
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
