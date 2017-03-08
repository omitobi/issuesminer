<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommitsFileChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commits_file_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('commit_id')->unsigned();
            $table->string('issue_id');  //from the URL
            $table->string('author_id');
            $table->string('author_name');
            $table->string('file');
            $table->string('api_url');
            $table->string('web_url'); //web_url
            $table->string('date_changed');
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
        Schema::dropIfExists('commits_file_changes');
    }
}
