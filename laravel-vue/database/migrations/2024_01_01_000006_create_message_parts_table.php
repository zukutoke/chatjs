<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_parts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('message_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // text, reasoning, file, source-url, tool-call, etc.
            $table->integer('order')->default(0);

            // Text content
            $table->text('text_content')->nullable();

            // Reasoning content
            $table->text('reasoning_content')->nullable();

            // File content
            $table->string('file_media_type')->nullable();
            $table->string('file_filename')->nullable();
            $table->string('file_url')->nullable();

            // Source URL content
            $table->string('source_url')->nullable();
            $table->string('source_title')->nullable();
            $table->text('source_description')->nullable();

            // Tool call content
            $table->string('tool_name')->nullable();
            $table->string('tool_call_id')->nullable();
            $table->enum('tool_state', ['pending', 'running', 'completed', 'failed'])->nullable();
            $table->json('tool_input')->nullable();
            $table->json('tool_output')->nullable();

            // Provider metadata
            $table->json('provider_metadata')->nullable();

            $table->timestamps();

            $table->index('message_id');
            $table->index(['message_id', 'order']);
            $table->index(['message_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_parts');
    }
};
