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
        Schema::create('chatbot_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained();
            $table->string('event_type'); // message, error, user_input, etc.
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->string('source_domain')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_usage_logs');
    }
};
