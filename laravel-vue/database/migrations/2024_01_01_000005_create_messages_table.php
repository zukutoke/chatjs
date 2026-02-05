<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('chat_id')->constrained()->cascadeOnDelete();
            $table->uuid('parent_message_id')->nullable();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->json('attachments')->nullable();
            $table->string('selected_model')->nullable();
            $table->string('selected_tool')->nullable();
            $table->string('active_stream_id')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->json('last_context')->nullable();
            $table->json('annotations')->nullable();
            $table->timestamps();

            $table->index('chat_id');
            $table->index('parent_message_id');
            $table->index(['chat_id', 'created_at']);

            $table->foreign('parent_message_id')
                ->references('id')
                ->on('messages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
