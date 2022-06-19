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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('one-to-one');
            $table->string('content')->nullable();
            $table->string('status')->nullable();

            $table->unsignedBigInteger('to_uid')->nullable();
            $table->unsignedBigInteger('from_uid')->nullable();
            $table->unsignedBigInteger('to_forum_id')->nullable();
            $table->unsignedBigInteger('chat_id')->nullable();

            $table->timestamps();

            $table->index('to_uid');
            $table->index('from_uid');
            $table->index('to_forum_id');
            $table->index('chat_id');
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
};
