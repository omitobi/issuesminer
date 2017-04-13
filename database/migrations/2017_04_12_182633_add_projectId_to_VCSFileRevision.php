<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToVCSFileRevision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCSFileRevision', function (Blueprint $table) {

            $table->bigInteger('ProjectId')->unsigned()->after('Id');
            $table->foreign('ProjectId')->references('Id')->on('VCSProject');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('VCSFileRevision', function (Blueprint $table) {
            $table->dropColumn('ProjectId');
        });
    }
}
