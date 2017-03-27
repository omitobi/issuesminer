<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('bug_labels');
            $table->string('issue_state')->default('closed');
            $table->string('since')->default('2016-03-24');
            $table->string('until')->default('2017-03-25');
            $table->string('sort')->default('created');
            $table->string('direction')->default('asc');
            $table->string('pr_state')->default('merged');
            $table->string('per_page')->default(100);
            $table->string('pr_approved_state');
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
        Schema::dropIfExists('generals');
    }
}
