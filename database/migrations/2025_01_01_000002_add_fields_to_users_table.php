<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 20)->unique()->nullable()->after('name');
            $table->boolean('is_admin')->default(false)->after('email');
            $table->boolean('is_banned')->default(false)->after('is_admin');
            $table->text('ban_reason')->nullable()->after('is_banned');
            $table->timestamp('last_login_at')->nullable()->after('ban_reason');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'is_admin', 'is_banned', 'ban_reason', 'last_login_at', 'last_login_ip']);
        });
    }
};
