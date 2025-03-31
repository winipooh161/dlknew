<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('chat_group_id')->nullable()->constrained('chat_groups')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->text('attachments')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['sender_id', 'receiver_id']);
            $table->index(['chat_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
