<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectcostdifferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projectcostdifference', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->integer('ProjectId')->unsigned();
            $table->integer('ModuleLevel')->unsigned();
            $table->string('ModulePath');
            $table->string('ModulePath2');
            $table->bigInteger('AlternativeCost')->signed();
            $table->bigInteger('AlternativeCost2')->signed();
            $table->bigInteger('costDifference')->unsigned();
            $table->integer('costCompare')->unsigned();
            $table->integer('fixesDifference')->unsigned();
            $table->integer('fixesCompare')->unsigned();
            $table->integer('loc1')->unsigned()->default(0);
            $table->integer('loc2')->unsigned()->default(0);
            $table->integer('fixes')->unsigned()->default(0);
            $table->integer('fixes2')->unsigned()->default(0);
            $table->date('Date')->unsigned()->default(0);
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
        Schema::dropIfExists('projectcostdifference');
    }
}
