<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('kill_count')->default(0);
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['character_id', 'quest_id']); // her görev bir kez
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_quests');
    }
};
