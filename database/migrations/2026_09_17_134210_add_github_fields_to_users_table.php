<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('github_id')->nullable()->unique()->after('email');
            $table->string('github_nickname')->nullable()->after('github_id');
            $table->string('github_avatar')->nullable()->after('github_nickname');
            // Encrypted at the app layer (User::casts()) — this token grants
            // repo-scoped access to the user's GitHub account.
            $table->text('github_token')->nullable()->after('github_avatar');
            // GitHub OAuth login allows passwordless authentication.
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['github_id', 'github_nickname', 'github_avatar', 'github_token']);
        });
    }
};
