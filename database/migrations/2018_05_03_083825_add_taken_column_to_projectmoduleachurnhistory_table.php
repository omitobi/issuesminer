<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTakenColumnToProjectmoduleachurnhistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projectmoduleachurnhistory', function (Blueprint $table) {
            $table->boolean('taken')->default(false)->after('loc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projectmoduleachurnhistory', function (Blueprint $table) {
            $table->dropColumn('taken');
        });
    }
}
