<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSEstimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSEstimations', function (Blueprint $table) {
            $table->increments('Id');
//            $table->bigInteger('ProjectDateRevisionId')->unique()->unsigned();
            $table->bigInteger('ProjectId')->unsigned();
            $table->dateTime('Date')->unique();
            $table->decimal('Avg_Previous_Imp_Commits', 38, 6)->nullable();
            $table->decimal('Avg_Previous_OO_Commits', 38, 6)->nullable();
            $table->decimal('Avg_Previous_XML_Commits', 38, 6)->nullable();
            $table->decimal('Avg_Previous_XSL_Commits', 38, 6)->nullable();
            $table->integer('Committer_Previous_Commits')->nullable();
            $table->integer('Committer_Previous_Imp_Commits')->nullable();
            $table->integer('Committer_Previous_OO_Commits')->nullable();
            $table->integer('Committer_Previous_XML_Commits')->nullable();
            $table->integer('Committer_Previous_XSL_Commits')->nullable();
            $table->integer('Developers_On_Project_To_Date')->nullable();
            $table->integer('Imp_Developers_On_Project_To_Date')->nullable();
            $table->integer('Imperative_Files')->nullable();
            $table->integer('OO_Developers_On_Project_To_Date')->nullable();
            $table->integer('OO_Files')->nullable();
            $table->integer('Total_Developers')->nullable();
            $table->integer('Total_Imp_Developers')->nullable();
            $table->integer('Total_OO_Developers')->nullable();
            $table->integer('Total_XML_Developers')->nullable();
            $table->integer('Total_XSL_Developers')->nullable();
            $table->integer('XML_Developers_On_Project_To_Date')->nullable();
            $table->integer('XML_Files')->nullable();
            $table->integer('XSL_Developers_On_Project_To_Date')->nullable();
            $table->integer('XSL_Files')->nullable();
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
        Schema::dropIfExists('VCSEstimations');
    }
}
