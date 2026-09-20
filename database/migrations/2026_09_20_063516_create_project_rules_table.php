<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Ссылается на ключ App\Support\ChecksCatalog, не на отдельную таблицу:
            // каталог чеков фиксированный (toggle-список из конструктора),
            // а не пользовательский произвольный ввод, поэтому FK тут не нужен.
            $table->string('check_id');
            $table->timestamps();

            $table->unique(['project_id', 'check_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_rules');
    }
};
