<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->uuid('chat_id');
            $table->uuid('message_id');
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_upvoted');
            $table->timestamps();

            $table->primary(['chat_id', 'message_id', 'user_id']);

            $table->foreign('chat_id')
                ->references('id')
                ->on('chats')
                ->cascadeOnDelete();

            $table->foreign('message_id')
                ->references('id')
                ->on('messages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
