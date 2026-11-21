<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retired_tokens', function (Blueprint $table) {
            $table->id();
            $table->char('token_hash', 64)->unique();
            // No foreign key on purpose: the row must outlive the project, so a
            // deleted project's token stays distinguishable from an unknown one.
            $table->unsignedBigInteger('project_id')->index();
            $table->timestamp('retired_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retired_tokens');
    }
};
