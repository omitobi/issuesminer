<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCS_modules', function (Blueprint $table) {
            $table->bigIncrements('ModuleDateRevisionId');

            $table->bigInteger('ProjectDateRevisionId')->unsigned();

            $table->string('ModuleId');
            $table->integer('Files')->unsigned();
            $table->integer('XMLFiles')->unsigned();
            $table->integer('XLSFiles')->unsigned();
            $table->integer('ImperativeFiles')->unsigned();

            $table->integer('JavaFiles')->unsigned();
            $table->integer('CPPFiles')->unsigned();
            $table->integer('CFiles')->unsigned();
            $table->integer('CSharpFiles')->unsigned();
            $table->integer('PHPFiles')->unsigned();
            $table->integer('JavaScriptFiles')->unsigned();
            $table->integer('RubyFiles')->unsigned();

            $table->string('ModulePath');
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
        Schema::dropIfExists('VCS_modules');
    }
}
