<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVcsEstimationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vcs_estimation', function (Blueprint $table) {
            $table->increments('id');
            $table->string('Avg_Previous_Imp_Commits');
            $table->string('Avg_Previous_OO_Commits');
            $table->string('Avg_Previous_XML_Commits');
            $table->string('Avg_Previous_XSL_Commits');
            $table->string('Committer_Previous_Commits');
            $table->string('Committer_Previous_Imp_Commits');
            $table->string('Committer_Previous_OO_Commits');
            $table->string('Committer_Previous_XML_Commits');
            $table->string('Committer_Previous_XSL_Commits');
            $table->string('Developers_On_Project_To_Date');
            $table->string('Imp_Developers_On_Project_To_Date');
            $table->string('Imperative_Files');
            $table->string('OO_Developers_On_Project_To_Date');
            $table->string('OO_Files');
            $table->string('Total_Developers');
            $table->string('Total_Imp_Developers');
            $table->string('Total_OO_Developers');
            $table->string('Total_XML_Developers');
            $table->string('Total_XSL_Developers');
            $table->string('XML_Developers_On_Project_To_Date');
            $table->string('XML_Files');
            $table->string('XSL_Developers_On_Project_To_Date');
            $table->string('XSL_Files');
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
        Schema::dropIfExists('vcs_estimation');
    }
}
