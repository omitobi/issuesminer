<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectYearlyLocChurnToVCSEstimations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCSEstimations', function (Blueprint $table) {
            $table->bigInteger('ProjectYearlyLOCChurn')->default(0);
//            $table->addColumn('bigInteger','ProjectYearlyLOCChurn')->default(0);
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
            $table->removeColumn('ProjectYearlyLOCChurn');
        });
    }
}
