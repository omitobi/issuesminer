<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseCommitsIdSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commits', function (Blueprint $table) {
            $table->bigIncrements('id' )->change();
        });

        Schema::table('commits_file_changes', function (Blueprint $table) {
            $table->bigIncrements('id' )->change();
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->bigIncrements('id' )->change();
        });

        Schema::table('issues_commits', function (Blueprint $table) {
            $table->bigIncrements('id' )->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commits', function (Blueprint $table) {
            $table->increments('id')->change(true);
        });
        Schema::table('commits_file_changes', function (Blueprint $table) {
            $table->increments('id')->change(true);
        });
        Schema::table('issues', function (Blueprint $table) {
            $table->increments('id')->change(true);
        });
        Schema::table('issues_commits', function (Blueprint $table) {
            $table->increments('id')->change(true);
        });
    }
}
