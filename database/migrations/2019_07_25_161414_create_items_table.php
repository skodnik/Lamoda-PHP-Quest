<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('names', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });

        Schema::create('containers', function (Blueprint $table) {
            if ($_ENV['INCREMENT_IDS']) {
                $table->bigIncrements('id');
            } else {
                $table->unsignedBigInteger('id')->unique();
            }
            $table->string('name');
        });

        Schema::create('items', function (Blueprint $table) {
            if ($_ENV['INCREMENT_IDS']) {
                $table->bigIncrements('id');
            } else {
                $table->unsignedBigInteger('id')->unique();
            }
            $table->unsignedBigInteger('container_id');
            $table->unsignedBigInteger('name_id');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('name_id')->references('id')->on('names')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('names');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('items');
    }
}
