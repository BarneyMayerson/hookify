<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('github_scopes')->nullable()->after('github_token');
        });

        // Every token stored so far was requested with `repo`.
        DB::table('users')
            ->whereNotNull('github_token')
            ->update(['github_scopes' => json_encode(['user:email', 'repo'])]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('github_scopes');
        });
    }
};
