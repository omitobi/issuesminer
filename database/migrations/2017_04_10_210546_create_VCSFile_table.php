<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSFile', function (Blueprint $table) {
            $table->bigIncrements('Id');

            $table->string('Name');


            $table->bigInteger('ProjectId')->unsigned();
            $table->foreign('ProjectId')->references('Id')->on('VCSProject');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('VCSFile');
    }
}
