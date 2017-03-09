<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identifier')->unique();
            $table->string('organization_name');
            $table->string('name')->unique();
            $table->string('type')->default('framework'); //maybe framework or something else?
            $table->string('language');
            $table->string('description');
            $table->string('homepage');
            $table->string('api_url')->unique();
            $table->string('web_url')->unique();
            $table->string('commits_url')->unique();
            $table->string('issues_url')->unique();
            $table->string('prs_url')->unique();
            $table->string('date_created');
            $table->string('default_branch');
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
        Schema::dropIfExists('projects');
    }
}
