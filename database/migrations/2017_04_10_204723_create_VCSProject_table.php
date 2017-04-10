<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSProject', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');

            $table->integer('SystemId')->unsigned();
            $table->foreign('SystemId')->references('Id')->on('VCSSystem');

            $table->string('Location');
            $table->string('Type');

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
        Schema::dropIfExists('VCSProject');
    }
}
