<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRevisionNumberToVCSEstimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCSEstimations', function (Blueprint $table) {
            $table->integer('RevisionNumber')->unsigned()->nullable(false)->after('Date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('VCSEstimations', function (Blueprint $table) {
            $table->dropColumn('RevisionNumber');
        });
    }
}
