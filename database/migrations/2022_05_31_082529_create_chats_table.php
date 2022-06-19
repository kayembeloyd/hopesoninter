<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('type');

            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('chat_with_id')->nullable();
            $table->unsignedBigInteger('forum_id')->nullable();
            $table->unsignedBigInteger('last_message_id')->nullable();

            $table->timestamps();

            $table->index('owner_id')->nullable();
            $table->index('chat_with_id')->nullable();
            $table->index('forum_id')->nullable();
            $table->index('last_message_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
