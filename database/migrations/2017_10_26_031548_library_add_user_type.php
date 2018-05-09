<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LibraryAddUserType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('libraries', function (Blueprint $table) {
            $table->text('intro')->after('logo')->nullable()->comment('图书馆简介');
            $table->integer('user_id')->after('id')->comment('图书ID');
			$table->enum('type', ['FAMILY', 'ORG', 'STORE'])->default('FAMILY')->after('user_id')->comment('图书馆类型'); 
            $table->string('logo')->default('https://images.ufutx.com/201710/26/7cd9f274cd861fbb2ce30a99555f00bb.png')->change();
            $table->string('name')->unique()->change();
	    }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('libraries', function (Blueprint $table) {
			$table->dropColumn('user_id', 'type', 'intro');
	    }); 
    }
}
