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
        Schema::table('chatbots', function (Blueprint $table) {
            $table->string('openai_model', 50)->default('gpt-4o-mini');
            $table->integer('total_messages')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->timestamp('last_interaction')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn([
                'openai_model',
                'total_messages',
                'total_tokens',
                'last_interaction',
            ]);
        });
    }
};
