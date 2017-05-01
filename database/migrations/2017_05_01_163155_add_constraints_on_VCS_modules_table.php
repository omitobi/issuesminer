<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConstraintsOnVCSModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCS_modules', function (Blueprint $table) {
            $table->unique(
                ['ProjectId', 'ProjectDateRevisionId', 'ModulePath']
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('VCS_modules', function (Blueprint $table) {
            $table->dropUnique(['ProjectId', 'ProjectDateRevisionId', 'ModulePath']);
        });
    }
}
