<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProjectsTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->string('private')->after('type');
            $table->string('size');
            $table->string('merges_url');
            $table->string('labels_url');
            $table->string('languages_url');
            $table->string('contributors_url');
            $table->string('clone_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('private');
            $table->dropColumn('size');
            $table->dropColumn('merges_url');
            $table->dropColumn('labels_url');
            $table->dropColumn('languages_url');
            $table->dropColumn('contributors_url');
            $table->dropColumn('clone_url');
        });
    }
}
