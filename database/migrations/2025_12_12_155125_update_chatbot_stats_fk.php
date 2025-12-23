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

        Schema::table('chatbot_stats', function (Blueprint $table) {
            $table->dropForeign(['chatbot_id']);

            $table->foreign('chatbot_id')
                ->references('id')
                ->on('chatbots')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_stats', function (Blueprint $table) {
            $table->dropForeign(['chatbot_id']);
            $table->foreign('chatbot_id')
                ->references('id')
                ->on('chatbots');
        });

    }
};
