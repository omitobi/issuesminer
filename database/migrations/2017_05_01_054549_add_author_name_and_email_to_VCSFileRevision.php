<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuthorNameAndEmailToVCSFileRevision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCSFileRevision', function (Blueprint $table) {
            $table->string('AuthorEmail')->nullable();
            $table->string('AuthorName')->nullable();
            $table->integer('AddedCodeLines')->unsigned()->default(0);
            $table->integer('RemovedCodeLines')->unsigned()->default(0);
            $table->integer('LinesOfCode')->unsigned()->default(0);
            $table->string('status')->nullable();
            $table->integer('changes')->nullable();
            $table->longText('ContentsU')->nullable();
            $table->longText('patch')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('VCSFileRevision', function (Blueprint $table) {
            $table->dropColumn('AuthorEmail');
            $table->dropColumn('AuthorName');
            $table->dropColumn('AddedCodeLines');
            $table->dropColumn('RemovedCodeLines');
            $table->dropColumn('LinesOfCode');
            $table->dropColumn('status');
            $table->dropColumn('changes');
            $table->dropColumn('ContentsU');
            $table->dropColumn('patch');
        });
    }
}
