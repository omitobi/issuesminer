<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocToProjectmoduleachurnhistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projectmoduleachurnhistory', function (Blueprint $table) {
            $table->bigInteger('loc')->unsigned()->default(0)->nullable();
        });
    }

/*
 -- this table must have been created
CREATE TABLE projectmoduleachurnhistory
(
Id              INT AUTO_INCREMENT
PRIMARY KEY,
Ids             INT             NULL,
ProjectId       INT DEFAULT '0' NULL,
ModulePath      VARCHAR(255)    NULL,
ModuleLevel     INT             NULL,
AlternativeCost BIGINT          NULL,
fixes           INT DEFAULT '0' NULL,
created_at      TIMESTAMP       NULL,
updated_at      TIMESTAMP       NULL,
Date            DATE            NULL
);
    */

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projectmoduleachurnhistory', function (Blueprint $table) {
            $table->dropColumn('loc');
        });
    }
}
