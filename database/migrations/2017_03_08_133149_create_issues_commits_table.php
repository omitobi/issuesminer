<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssuesCommitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues_commits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('issue_id');
            $table->string('pr_id');
            $table->string('commit_sha');  //from the URL
            $table->string('author_id');
            $table->string('author_name');
            $table->string('api_url');
            $table->string('web_url');
            $table->text('description');
            $table->integer('file_changed_count')->unsigned(); //optional?
            $table->string('date_committed');
            $table->string('files_retrieved')->default('0');
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
        Schema::dropIfExists('issues_commits');
    }
}
