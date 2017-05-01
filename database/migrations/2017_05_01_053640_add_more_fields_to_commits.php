<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreFieldsToCommits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commits', function (Blueprint $table) {
            $table->string('author_email')->nullable();
            $table->string('author_username')->nullable();
//            $table->string('author_name')->nullable();
            $table->integer('file_added')->unsigned()->default(0);
            $table->integer('file_removed')->unsigned()->default(0);
            $table->integer('file_modified')->unsigned()->default(0);
            $table->integer('additions')->unsigned()->default(0);
            $table->integer('deletions')->unsigned()->default(0);
            $table->integer('total')->unsigned()->default(0);
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
            $table->dropColumn('author_email');
            $table->dropColumn('author_username');
//            $table->dropColumn('author_name');
            $table->dropColumn('file_added');
            $table->dropColumn('file_removed');
            $table->dropColumn('file_modified');
            $table->dropColumn('additions');
            $table->dropColumn('deletions');
            $table->dropColumn('total');
        });
    }
}
