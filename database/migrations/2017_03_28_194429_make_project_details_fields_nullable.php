<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeProjectDetailsFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_details', function (Blueprint $table) {

            $table->string('issues_labels')->nullable(true)->change(); //comma separate labels
            $table->string('files_types')->nullable(true)->change(); //php, xml, js
            $table->string('languages')->nullable(true)->change(); //php, javascript, java
            $table->string('main_branch')->nullable(true)->change(); //e.g master
            $table->string('total_developers')->nullable(true)->change(); //retrievable from contributors?
            $table->string('total_commits')->nullable(true)->change(); //numbers
            $table->string('total_issues')->nullable(true)->change(); //numbers
            $table->string('total_bug_issues')->nullable(true)->change(); //bugs related issues number
            $table->string('total_closed_bug_issues')->default(0)->nullable(true)->change(); //bugs related issues number
            $table->string('total_created_files')->default(0)->nullable(true)->change(); //bugs related issues number
            $table->string('total_modified_files')->default(0)->nullable(true)->change(); //bugs related issues number
            $table->string('total_deleted_files')->default(0)->nullable(true)->change();//bugs related issues number
            $table->string('total_deletions')->default(0)->nullable(true)->change(); //bugs related issues number
            $table->string('total_additions')->default(0)->nullable(true)->change(); //bugs related issues number

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_details', function (Blueprint $table) {
            $table->string('issues_labels')->nullable(false)->change(); //comma separate labels
            $table->string('files_types')->nullable(false)->change(); //php, xml, js
            $table->string('languages')->nullable(false)->change(); //php, javascript, java
            $table->string('main_branch')->nullable(false)->change(); //e.g master
            $table->string('total_developers')->nullable(false)->change(); //retrievable from contributors?
            $table->string('total_commits')->nullable(false)->change(); //numbers
            $table->string('total_issues')->nullable(false)->change(); //numbers
            $table->string('total_bug_issues')->nullable(false)->change(); //bugs related issues number
            $table->string('total_closed_bug_issues')->default(0)->nullable(false)->change(); //bugs related issues number
            $table->string('total_created_files')->default(0)->nullable(false)->change();//bugs related issues number
            $table->string('total_modified_files')->default(0)->nullable(false)->change();//bugs related issues number
            $table->string('total_deleted_files')->default(0)->nullable(false)->change(); //bugs related issues number
            $table->string('total_deletions')->default(0)->nullable(false)->change();//bugs related issues number
            $table->string('total_additions')->default(0)->nullable(false)->change(); //bugs related issues number
        });
    }
}
