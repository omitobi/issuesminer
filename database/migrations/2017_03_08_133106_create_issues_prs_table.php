<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssuesPrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues_prs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('issue_id');
            $table->string('pr_id');
            $table->string('commits_counts');
            $table->string('merged_status'); //**very important to filter merged prs
//            $table->string('message');
            $table->string('author_id');
            $table->string('author_name');
            $table->string('title');
            $table->string('description');
            $table->string('api_url');
            $table->string('web_url');
            $table->enum('state',['open', 'closed']);
            $table->string('date_created');
            $table->string('date_updated');
            $table->string('date_closed');
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
        Schema::dropIfExists('issues_prs');
    }
}
