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
        Schema::table('chatbot_usage_logs', function (Blueprint $table) {
            $table->string('session_id', 100)->nullable()->after('chatbot_id');
            $table->string('ip_address', 45)->nullable()->after('session_id');
            $table->string('user_agent', 500)->nullable()->after('ip_address');
            $table->json('metadata')->nullable()->after('user_agent');

            // Índices para optimizar consultas
            $table->index(['chatbot_id', 'session_id']);
            $table->index(['session_id']);
            $table->index(['created_at']);
            $table->index(['chatbot_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['chatbot_id', 'session_id']);
            $table->dropIndex(['session_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['chatbot_id', 'created_at']);
            $table->dropColumn(['session_id', 'ip_address', 'user_agent', 'metadata']);
        });
    }
};
