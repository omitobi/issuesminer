<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectDateRevisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ProjectDateRevision', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->integer('ProjectId')->unsigned()->nullable(false);
            $table->dateTime('Date')->nullable(false);
            $table->bigInteger('CommitId')->unsigned()->nullable(false);
            $table->string('CommitterId')->nullable(false);
            $table->bigInteger('RevisionId')->unsigned()->nullable(false);
            $table->string('Extension')->nullable();
            $table->bigInteger('FiletypeId')->unsigned()->nullable(false);

            $table->bigInteger('estimation_touched')->unsigned()->default(0);
            $table->bigInteger('module_touched')->unsigned()->default(0);
            $table->timestamps();

            $table->unique(['ProjectId', 'Date'], 'project_date_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ProjectDateRevision');
    }
}
