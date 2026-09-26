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
            // References a key from App\Support\ChecksCatalog rather than a dedicated table:
            // the catalog is fixed (a predefined toggle list from the constructor)
            // rather than arbitrary user input, so an FK is not required here.
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
