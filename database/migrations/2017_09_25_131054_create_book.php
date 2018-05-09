<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_id')->references('id')->on('classes');
            $table->string('title')->nullable();
            $table->string('origin_title')->nullable();
            $table->string('author')->nullable();
            $table->string('translator')->nullable();
            $table->string('image')->nullable();
            $table->text('summary')->nullable();
            $table->string('publisher')->nullable();
            $table->decimal('price', 9, 2)->nullable();
            $table->string('isbn');
            $table->string('pubdate', 20)->nullable();
            $table->integer('pages')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
