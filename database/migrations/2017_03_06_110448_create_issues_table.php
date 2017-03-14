<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('identifier')->unsigned()->unique();
            $table->integer('number')->unsigned();
            $table->string('title');
            $table->string('reporter_name');
            $table->string('state', 10)->nullable(true);
            $table->string('type', 10)->nullable(true);
            $table->string('description')->nullable(true);
            $table->string('api_url');
            $table->string('web_url');
            $table->string('pr_url')->nullable(true);
            $table->string('date_created');
            $table->string('date_updated');
            $table->string('date_closed')->nullable(true);
            $table->string('pr_retrieved')->default(0);
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
        Schema::dropIfExists('issues');
    }
}
