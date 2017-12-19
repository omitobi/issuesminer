<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateToVCSModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCS_Modules', function (Blueprint $table) {
            $table->dateTime('Date')->after('ProjectDateRevisionId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('VCS_Modules', function (Blueprint $table) {
            $table->dropColumn('Date');
        });
    }
}
