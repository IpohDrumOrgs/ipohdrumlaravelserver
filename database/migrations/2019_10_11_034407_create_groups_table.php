<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->unsignedInteger('company_id')->unsigned();
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('desc')->nullable();
            $table->boolean('status')->default(1);
            $table->string('lastedit_by')->nullable();
            $table->timestamps();
            
            $table->foreign('company_id')
            ->references('id')
            ->on('companies')
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
        Schema::dropIfExists('groups');
    }
}
