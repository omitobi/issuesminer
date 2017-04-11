<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSFiletypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSFiletypes', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Type');
            $table->boolean('IsText');
            $table->boolean('IsXML');
            $table->boolean('IsImperative');
            $table->boolean('IsOO');
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
        Schema::dropIfExists('VCSFiletypes');
    }
}
/**
 * Schema::create('VCSExtensions', function (Blueprint $table) {
$table->increments('Id');
$table->string('Extension');
$table->string('Type');
$table->boolean('IsText');
$table->boolean('IsXML');
$table->integer('TypeId')->unsigned();
$table->timestamps();
});
 */