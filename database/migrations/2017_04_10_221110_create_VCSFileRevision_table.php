<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSFileRevisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSFileRevision', function (Blueprint $table) {
            $table->bigIncrements('Id');

            $table->string('Name');

            $table->bigInteger('FileId')->unsigned();
            $table->foreign('FileId')->references('Id')->on('VCSFile');

            $table->dateTimeTz('Date');
            $table->longText('Comment');

            $table->bigInteger('PreviousRevisionId')->unsigned();
            $table->foreign('PreviousRevisionId')->references('Id')->on('VCSFileRevision');

            $table->string('Alias');

            $table->bigInteger('ProjectLOC')->unsigned();
            $table->string('CommitterId');
            $table->string('Extension');
            $table->string('ExtensionId');

            $table->integer('FiletypeId')->unsigned();
            $table->foreign('FiletypeId')->references('Id')->on('VCSFileTypes');


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
        Schema::dropIfExists('VCSFileRevision');
    }
}
