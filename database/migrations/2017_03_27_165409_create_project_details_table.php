<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_details', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('project_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects');

            $table->string('issues_labels'); //comma separate labels
            $table->string('files_types'); //php, xml, js
            $table->string('languages'); //php, javascript, java
            $table->string('main_branch'); //e.g master
            $table->string('total_developers'); //retrievable from contributors?
            $table->string('total_commits'); //numbers
            $table->string('total_issues'); //numbers
            $table->string('total_bug_issues'); //bugs related issues number
            $table->string('total_closed_bug_issues')->default(0); //bugs related issues number
            $table->string('total_created_files')->default(0); //bugs related issues number
            $table->string('total_modified_files')->default(0); //bugs related issues number
            $table->string('total_deleted_files')->default(0); //bugs related issues number
            $table->string('total_deletions')->default(0); //bugs related issues number
            $table->string('total_additions')->default(0); //bugs related issues number
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
        Schema::dropIfExists('project_details');
    }
}
