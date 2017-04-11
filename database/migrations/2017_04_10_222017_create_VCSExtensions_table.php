<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSExtensions', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Extension');
            $table->string('Type');
            $table->boolean('IsText');
            $table->boolean('IsXML');
            $table->integer('TypeId')->unsigned();
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
        Schema::dropIfExists('VCSExtensions');
    }
}
