<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPurchasedVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_purchased_video', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('video_id')->unsigned();
            $table->unsignedInteger('user_id')->unsigned();
            $table->boolean('saved')->default(false);
            $table->boolean('purchased')->default(false);
            $table->string('watchedlength')->default('0:00');
            $table->string('likestatus')->nullable();
            $table->string('status')->default('clicked');
            $table->timestamps();

            $table->foreign('video_id')
            ->references('id')
            ->on('videos')
            ->onUpdate('cascade')
            ->onDelete('restrict');

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onUpdate('cascade')
            ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_purchased_video');
    }
}
