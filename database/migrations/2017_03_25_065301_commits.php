<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Commits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('issue_id')->nullable()->default(0);
            $table->string('pr_id')->nullable()->default(0);
            $table->string('commit_sha');  //from the URL
            $table->string('author_id');
            $table->string('author_name');
            $table->string('api_url');
            $table->string('web_url');
            $table->text('description');
            $table->integer('comment_count')->unsigned()->default(0);
            $table->integer('file_changed_count')->unsigned()->default(0); //optional?
            $table->string('date_committed');
            $table->string('touched')->default('0');
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
        Schema::dropIfExists('commits');
    }
}
