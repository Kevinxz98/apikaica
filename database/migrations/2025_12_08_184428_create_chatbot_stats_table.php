<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbot_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained();
            $table->integer('messages_count')->default(0);
            $table->integer('conversations_count')->default(0);
            $table->integer('users_count')->default(0);
            $table->integer('tokens_used_month')->default(0);
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_stats');
    }
};
