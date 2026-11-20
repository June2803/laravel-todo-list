<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_tags', function (Blueprint $table) {
            $table->collation('utf8mb4_bin');
            $table->id();
            $table->foreignId('todo_id')->constrained()->cascadeOnDelete();
            $table->string('name', 40)->index();
            $table->unique(['todo_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_tags');
    }
};
