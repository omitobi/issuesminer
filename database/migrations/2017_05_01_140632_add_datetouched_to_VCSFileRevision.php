<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDatetouchedToVCSFileRevision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('VCSFileRevision', function (Blueprint $table) {
            $table->bigInteger('datetouched')->default(0);
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
            $table->dropColumn('datetouched');
        });
    }
}
