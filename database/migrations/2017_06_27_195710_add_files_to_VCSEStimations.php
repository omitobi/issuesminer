<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFilesToVCSEStimations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCSEstimations', function (Blueprint $table) {
            $table->integer('Files')->unsigned()->default(0)->after('Imp_Developers_On_Project_To_Date');
            $table->string('DevelopmentStageAsPercent')->nullable()->after('XSL_Files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('VCSEstimations', function (Blueprint $table) {
            $table->dropColumn('Files');
            $table->dropColumn('DevelopmentStageAsPercent');
        });
    }
}
