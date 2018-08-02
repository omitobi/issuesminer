<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocDifferenceAndLocCompareToProjectcostdifferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projectcostdifference', function (Blueprint $table) {
            $table->integer('locDifference')->unsigned();
            $table->integer('locCompare')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projectcostdifference', function (Blueprint $table) {
            $table->dropColumn('locDifference');
            $table->dropColumn('locCompare');
        });
    }
}
