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
            $table->integer('identifier')->unsigned()->unique();
            $table->integer('number')->unsigned();
            $table->string('title');
            $table->string('reporter_name');
            $table->string('state', 10);
            $table->string('url');
            $table->string('description');
            $table->string('pr_url');
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
        Schema::dropIfExists('issues');
    }
}
