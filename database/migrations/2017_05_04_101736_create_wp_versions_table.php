<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWpVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wp_versions', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('project_id')->unsigned();
          $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
          $table->integer('timestamp');
          $table->char('version', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wp_versions');
    }
}
