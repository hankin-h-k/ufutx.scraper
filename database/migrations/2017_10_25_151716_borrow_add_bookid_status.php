<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BorrowAddBookidStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('borrows', function (Blueprint $table) {
            $table->integer('book_id')->after('library_id')->comment('图书ID');
			$table->enum('status', ['RESERVE', 'BORROW', 'RETURN'])->default('RESERVE')->after('book_id')->comment('状态'); 
            $table->boolean('renew')->default(false)->change();
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
        Schema::table('borrows', function (Blueprint $table) {
			$table->dropColumn('book_id', 'status');
	    }); 
    }
}
