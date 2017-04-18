<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSTextFileRevisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSTextFileRevision', function (Blueprint $table) {
            $table->bigInteger('RevisionId')->unsigned();
            $table->foreign('RevisionId')->references('Id')->on('VCSFileRevision');

            $table->integer('CodeChurnLines')->unsigned()->defaul(NULL);
            $table->integer('AddedCodeLines')->unsigned()->defaul(0);
            $table->integer('RemovedCodeLines')->unsigned()->defaul(0);
            $table->integer('LinesOfCode')->unsigned()->defaul(0);

            $table->text('ContentsU');
            $table->text('CompressedContents');

            $table->bigInteger('CommitId')->unsigned();
            $table->integer('FileId')->unsigned();
            $table->integer('ProjectId')->unsigned();

            $table->timestamps();
            $table->primary('RevisionId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('VCSTextFileRevision');
    }
}
