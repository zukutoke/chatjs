<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('message_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->enum('kind', ['text', 'code', 'sheet'])->default('text');
            $table->string('language')->nullable(); // For code documents
            $table->timestamps();

            $table->index('user_id');
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
